<?php

declare(strict_types=1);

namespace Plattry\Event\Notification;

use EvLoop;

/**
 * An asynchronous notification event looper instance.
 */
class Looper implements LooperInterface
{
    /**
     * The base event loop.
     * @var EvLoop
     */
    protected EvLoop $loop;

    /**
     * The io-reader watcher.
     * @var Reader
     */
    protected Reader $reader;

    /**
     * The io-writer watcher.
     * @var Writer
     */
    protected Writer $writer;

    /**
     * The system signal watcher.
     * @var Signal
     */
    protected Signal $signal;

    /**
     * The timer watcher.
     * @var Timer
     */
    protected Timer $timer;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->loop = new EvLoop();

        $this->reader = new Reader($this->loop);
        $this->writer = new Writer($this->loop);
        $this->signal = new Signal($this->loop);
        $this->timer = new Timer($this->loop);
    }

    /**
     * @inheritDoc
     */
    public function reader(): Reader
    {
        return $this->reader;
    }

    /**
     * @inheritDoc
     */
    public function writer(): Writer
    {
        return $this->writer;
    }

    /**
     * @inheritDoc
     */
    public function signal(): Signal
    {
        return $this->signal;
    }

    /**
     * @inheritDoc
     */
    public function timer(): Timer
    {
        return $this->timer;
    }

    /**
     * @inheritDoc
     */
    public function watch(): void
    {
        $this->loop->run();
    }
}
