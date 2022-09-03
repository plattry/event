<?php

declare(strict_types=1);

namespace Plattry\Event\Network;

/**
 * Describe a protocol instance.
 */
interface ProtocolInterface
{
    /**
     * Check the packet length.
     * @param ConnectionInterface $connection
     * @param string $input
     * @return int
     */
    public function check(ConnectionInterface $connection, string $input): int;

    /**
     * Decode the raw message to custom data.
     * @param ConnectionInterface $connection
     * @param string $raw
     * @return mixed
     */
    public function decode(ConnectionInterface $connection, string $raw): mixed;

    /**
     * Encode the custom data to raw message.
     * @param ConnectionInterface $connection
     * @param mixed $data
     * @return string
     */
    public function encode(ConnectionInterface $connection, mixed $data): string;
}
