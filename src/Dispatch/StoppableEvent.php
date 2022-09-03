<?php

declare(strict_types=1);

namespace Plattry\Event\Dispatch;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * A stoppable event instance.
 */
class StoppableEvent extends Event implements StoppableEventInterface
{
    /**
     * Is stopped.
     * @var bool
     */
    protected bool $propagation_stopped = false;

    /**
     * Set the propagation is stopped or not.
     * @param bool $is_stopped
     * @return void
     */
    public function setPropagationStopped(bool $is_stopped): void
    {
        $this->propagation_stopped = $is_stopped;
    }

    /**
     * @inheritDoc
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagation_stopped;
    }
}
