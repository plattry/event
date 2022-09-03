<?php

declare(strict_types=1);

namespace Plattry\Event\Network;

/**
 * Describe a connection instance.
 */
interface ConnectionInterface
{
    /**
     * The connection id.
     */
    public const ATTR_ID = 'ID';

    /**
     * The local address.
     */
    public const ATTR_LOCAL_ADDR = 'LOCAL_ADDR';

    /**
     * The remote address.
     */
    public const ATTR_REMOTE_ADDR = 'REMOTE_ADDR';

    /**
     * Set the connection attribute.
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setAttribute(string $key, string $value): void;

    /**
     * Get the connection attribute with $key or all attributes.
     * @param string|null $key
     * @return string|array|null
     */
    public function getAttribute(string $key = null): string|array|null;

    /**
     * Accept the input buffer, and start communicating.
     * @param string|null $buffer
     * @return void
     */
    public function accept(string $buffer = null): void;

    /**
     * Send the data to output buffer.
     * @param mixed $data
     * @param bool $raw
     * @return void
     */
    public function send(mixed $data, bool $raw = false): void;
}
