<?php

declare(strict_types=1);

namespace Plattry\Event\Notification;

/**
 * A looper-aware instance.
 */
trait LooperAwareTrait
{
    /**
     * The dispatcher instance.
     * @var LooperInterface|null
     */
    protected LooperInterface|null $looper = null;

    /**
     * Set a looper.
     * @param LooperInterface|null $looper
     * @return void
     */
    public function setLooper(LooperInterface|null $looper): void
    {
        $this->looper = $looper;
    }
}
