<?php

declare(strict_types=1);

namespace Plattry\Event\Dispatch;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * A dispatcher-aware instance.
 */
trait DispatcherAwareTrait
{
    /**
     * The dispatcher instance.
     * @var EventDispatcherInterface|null
     */
    protected EventDispatcherInterface|null $dispatcher = null;

    /**
     * Set a dispatcher.
     * @param EventDispatcherInterface|null $dispatcher
     * @return void
     */
    public function setDispatcher(EventDispatcherInterface|null $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
}
