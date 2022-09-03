<?php

declare(strict_types=1);

namespace Plattry\Event\Network;

use Plattry\Event\Network\Event\EventFactory;
use Plattry\Event\Notification\LooperAwareTrait;
use RuntimeException;

/**
 * A tcp connection instance.
 */
class Tcp extends ConnectionAbstract
{
    use LooperAwareTrait;

    /**
     * Connection is initial.
     */
    public const STATUS_INITIAL = 0;

    /**
     * Connection is connecting.
     */
    public const STATUS_CONNECTING = 1;

    /**
     * Connection is established.
     */
    public const STATUS_ESTABLISHED = 1 << 1;

    /**
     * Connection is closing.
     */
    public const STATUS_CLOSING = 1 << 2;

    /**
     * Connection is closed.
     */
    public const STATUS_CLOSED = 1 << 3;

    /**
     * The connection pool.
     * @var Tcp[]
     */
    protected static array $pool = [];

    /**
     * The connection status.
     * @var int
     */
    protected int $status = self::STATUS_INITIAL;

    /**
     * The length of the current packet.
     * @var int
     */
    protected int $packet = 0;

    /**
     * @inheritDoc
     */
    public function __construct(mixed $fd, string $remote_address)
    {
        parent::__construct($fd, $remote_address);

        $this->status = self::STATUS_CONNECTING;
    }

    /**
     * Get a connection with $id or all connections.
     * @param string|null $id
     * @return Tcp[]|Tcp|null
     */
    public static function get(string $id = null): array|Tcp|null
    {
        if ($id !== null) {
            return self::$pool[$id] ?? null;
        }

        return self::$pool;
    }

    /**
     * Clear a connection with $id or all connections.
     * @param string|null $id
     * @return void
     */
    public static function clear(string $id = null): void
    {
        if ($id !== null) {
            self::$pool[$id]?->close();

            return;
        }

        foreach (self::$pool as $item) {
            $item->close();
        }
    }

    /**
     * Get the number of connections.
     * @return int
     */
    public static function count(): int
    {
        return count(self::$pool);
    }

    /**
     * The reader handler, asynchronous read message.
     */
    public function read(): void
    {
        if ($this->status !== self::STATUS_ESTABLISHED) {
            return;
        }

        $buffer = fread($this->fd, self::$reader_buffer_size);
        if ($buffer === "" || $buffer === false) {
            // Trigger error-event.
            if (!is_resource($this->fd) || feof($this->fd)) {
                $this->dispatcher->dispatch(EventFactory::createErrorEvent($this));
                $this->destroy();
            }

            return;
        }

        $this->looper->reader()->appendData($this->fd, $buffer);

        if ($this->packet === 0) {
            $packet = $this->protocol->check($this, $this->looper->reader()->getData($this->fd));
            if ($packet === 0) {
                return;
            }

            $this->packet = $packet;
        }

        if ($this->looper->reader()->dataLength($this->fd) >= $this->packet) {
            // Decode raw data and trigger message-event.
            $data = $this->protocol->decode($this, $this->looper->reader()->sliceData($this->fd, 0, $this->packet));
            $this->dispatcher->dispatch(EventFactory::createMessageEvent($this, $data));
            $this->packet = 0;
        }
    }

    /**
     * The writer handler, asynchronous write message.
     */
    public function write(): void
    {
        if ($this->status !== self::STATUS_ESTABLISHED && $this->status !== self::STATUS_CLOSING) {
            return;
        }

        if ($this->looper->writer()->dataLength($this->fd) > 0) {
            $len = fwrite($this->fd, $this->looper->writer()->getData($this->fd), self::$writer_buffer_size);
            if ($len === false) {
                // Trigger error-event.
                if (!is_resource($this->fd) || feof($this->fd)) {
                    $this->dispatcher->dispatch(EventFactory::createErrorEvent($this));
                    $this->destroy();
                }

                return;
            }

            $this->looper->writer()->sliceData($this->fd, 0, $len);
        }

        if ($this->looper->writer()->dataLength($this->fd) === 0) {
            if ($this->status === self::STATUS_CLOSING) {
                $this->destroy();
            } else {
                $this->looper->writer()->stop($this->fd);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function accept(string $buffer = null): void
    {
        if ($this->status !== self::STATUS_CONNECTING) {
            return;
        }

        parent::accept($buffer);

        $this->looper === null &&
        throw new RuntimeException("Looper is not specified in connection.");

        // Trigger connect-event.
        $this->dispatcher->dispatch(EventFactory::createConnectEvent($this));

        $this->status = self::STATUS_ESTABLISHED;

        // Init reader and writer.
        $this->looper->reader()->add($this->fd, [$this, "read"], "");
        $this->looper->writer()->add($this->fd, [$this, "write"], "");

        self::$pool[$this->attribute[self::ATTR_ID]] = $this;
    }

    /**
     * Get the current connection status.
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function send(mixed $data, bool $raw = false): void
    {
        if ($this->status !== self::STATUS_ESTABLISHED && $this->status !== self::STATUS_CLOSING) {
            return;
        }

        $this->looper->writer()->appendData(
            $this->fd,
            $raw ? strval($data) : $this->protocol->encode($this, $data)
        );

        $this->looper->writer()->start($this->fd);
    }

    /**
     * Close the current connection.
     */
    public function close(mixed $data = null, bool $raw = false): void
    {
        if ($this->status === self::STATUS_CLOSING && $this->status === self::STATUS_CLOSED) {
            return;
        }

        $this->status = self::STATUS_CLOSING;
        $this->looper->reader()->remove($this->fd);

        if ($data !== null) {
            $this->send($data, $raw);

            return;
        }

        if ($this->looper->writer()->dataLength($this->fd) === 0) {
            $this->destroy();
        }
    }

    /**
     * Destroy the current connection and close the socket.
     * @return void
     */
    protected function destroy(): void
    {
        if ($this->status === self::STATUS_CLOSED) {
            return;
        }

        $this->status = self::STATUS_CLOSED;

        $this->looper->reader()->remove($this->fd);
        $this->looper->writer()->remove($this->fd);

        fclose($this->fd);
        $this->fd = null;

        // Trigger close-event.
        $this->dispatcher && $this->dispatcher->dispatch(EventFactory::createCloseEvent($this));

        unset(self::$pool[spl_object_id($this)]);
    }

    /**
     * The destructor.
     */
    public function __destruct()
    {
        if ($this->status !== self::STATUS_CLOSED) {
            $this->destroy();
        }
    }
}
