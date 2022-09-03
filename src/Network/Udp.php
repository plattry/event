<?php


declare(strict_types=1);

namespace Plattry\Event\Network;

use InvalidArgumentException;
use Plattry\Event\Network\Event\EventFactory;

/**
 * A udp connection instance.
 */
class Udp extends ConnectionAbstract
{
    /**
     * @inheritDoc
     */
    public function accept(string $buffer = null): void
    {
        parent::accept($buffer);

        $packet = $this->protocol->check($this, $buffer);
        if ($packet === 0) {
            return;
        }

        // Decode raw data and trigger message-event.
        $data = $this->protocol->decode($this, substr($buffer, 0, $packet));
        $this->dispatcher->dispatch(EventFactory::createMessageEvent($this, $data));
    }

    /**
     * @inheritDoc
     */
    public function send(mixed $data, bool $raw = false): void
    {
        $buffer = $raw ? strval($data) : $this->protocol->encode($this, $data);
        if ($buffer === "") {
            return;
        }

        strlen($buffer) > self::$writer_buffer_size &&
        throw new InvalidArgumentException("Data is out of writer buffer size.");

        stream_socket_sendto($this->fd, $buffer, 0, $this->attribute[self::ATTR_REMOTE_ADDR]);
    }
}
