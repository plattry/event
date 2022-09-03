<?php

declare(strict_types=1);

namespace Plattry\Event\Network;

use Plattry\Event\Dispatch\DispatcherAwareTrait;
use RuntimeException;

/**
 * An abstract connection instance.
 */
abstract class ConnectionAbstract implements ConnectionInterface
{
    use ProtocolAwareTrait;
    use DispatcherAwareTrait;

    /**
     * The reader buffer size.
     * @var int
     */
    public static int $reader_buffer_size = 65535;

    /**
     * The writer buffer size.
     * @var int
     */
    public static int $writer_buffer_size = 65535;

    /**
     * The file describer.
     * @var mixed|resource
     */
    protected mixed $fd;

    /**
     * The connection attributes.
     * @var array
     */
    protected array $attribute;

    /**
     * The constructor.
     * @param mixed $fd
     * @param string $remote_address
     */
    public function __construct(mixed $fd, string $remote_address)
    {
        // Init socket.
        $this->fd = $fd;
        stream_set_blocking($this->fd, false);
        stream_set_read_buffer($this->fd, 0);

        // Init attributes.
        $this->attribute = [
            self::ATTR_ID => spl_object_id($this),
            self::ATTR_LOCAL_ADDR => (string)stream_socket_get_name($this->fd, false),
            self::ATTR_REMOTE_ADDR => $remote_address
        ];
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $key, string $value): void
    {
        $this->attribute[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $key = null): string|array|null
    {
        if ($key !== null) {
            return $this->attribute[$key] ?? null;
        }

        return $this->attribute;
    }

    /**
     * @inheritDoc
     */
    public function accept(string $buffer = null): void
    {
        $this->protocol === null &&
        throw new RuntimeException("Protocol is not specified in connection.");

        $this->dispatcher === null &&
        throw new RuntimeException("Dispatcher is not specified in connection.");
    }
}