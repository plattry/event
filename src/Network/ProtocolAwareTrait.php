<?php

declare(strict_types=1);

namespace Plattry\Event\Network;

/**
 * A protocol-aware instance.
 */
trait ProtocolAwareTrait
{
    /**
     * The protocol instance.
     * @var ProtocolInterface|null
     */
    protected ProtocolInterface|null $protocol = null;

    /**
     * Set a protocol.
     * @param ProtocolInterface|null $protocol
     * @return void
     */
    public function setProtocol(ProtocolInterface|null $protocol): void
    {
        $this->protocol = $protocol;
    }
}
