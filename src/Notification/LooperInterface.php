<?php

declare(strict_types=1);

namespace Plattry\Event\Notification;

/**
 * Describe an asynchronous notification event looper instance.
 */
interface LooperInterface
{
    /**
     * Get the io-reader watcher.
     * @return Reader
     */
    public function reader(): Reader;

    /**
     * Get the io-writer watcher.
     * @return Writer
     */
    public function writer(): Writer;

    /**
     * Get the system signal watcher.
     * @return Signal
     */
    public function signal(): Signal;

    /**
     * Get the timer watcher.
     * @return Timer
     */
    public function timer(): Timer;

    /**
     * Start watching event.
     * @return void
     */
    public function watch(): void;
}
