<?php

declare(strict_types=1);

namespace Plattry\Event\Notification;

use EvLoop;
use EvWatcher;

/**
 * Describe an abstract event watcher instance.
 */
abstract class WatcherAbstract
{
    /**
     * The base event loop.
     * @var EvLoop
     */
    protected EvLoop $loop;

    /**
     * The map of key and sub-watcher.
     * @var EvWatcher[]
     */
    protected array $pool = [];

    /**
     * The constructor.
     * @param EvLoop $loop
     */
    public function __construct(EvLoop &$loop)
    {
        $this->loop = &$loop;
    }

    /**
     * Get a key of sub-watcher.
     * @param mixed $id
     * @return int|string
     */
    protected static function key(mixed $id): int|string
    {
        return strval($id);
    }

    /**
     * Has a sub-watcher with $id.
     * @param mixed $id
     * @return bool
     */
    public function has(mixed $id): bool
    {
        $key = static::key($id);

        return isset($this->pool[$key]);
    }

    /**
     * Remove a sub-watcher with $id.
     * @param mixed $id
     * @return bool
     */
    public function remove(mixed $id): bool
    {
        $key = static::key($id);

        if (!isset($this->pool[$key])) {
            return false;
        }

        $this->pool[$key]->stop();

        unset($this->pool[$key]);

        return true;
    }

    /**
     * Start a sub-watcher with $id.
     * @param mixed $id
     * @return bool
     */
    public function start(mixed $id): bool
    {
        $key = static::key($id);

        if (!isset($this->pool[$key])) {
            return false;
        }

        $this->pool[$key]->start();

        return true;
    }

    /**
     * Stop a sub-watcher with $id.
     * @param mixed $id
     * @return bool
     */
    public function stop(mixed $id): bool
    {
        $key = static::key($id);

        if (!isset($this->pool[$key])) {
            return false;
        }

        $this->pool[$key]->stop();

        return true;
    }

    /**
     * Set the buffer of a sub-watcher with $id.
     * @param mixed $id
     * @param string|array|null $data
     * @return bool
     */
    public function setData(mixed $id, string|array|null $data): bool
    {
        $key = static::key($id);

        if (!isset($this->pool[$key])) {
            return false;
        }

        $this->pool[$key]->data = $data;

        return true;
    }

    /**
     * Get the buffer of a sub-watcher with $id.
     * @param mixed $id
     * @return string|array|null
     */
    public function getData(mixed $id): string|array|null
    {
        $key = static::key($id);

        return $this->pool[$key]?->data;
    }

    /**
     * Get the length of the buffer of a sub-watcher with $id.
     * @param mixed $id
     * @return int
     */
    public function dataLength(mixed $id): int
    {
        $key = static::key($id);

        if (!isset($this->pool[$key])) {
            return 0;
        }

        return match (gettype($this->pool[$key]->data)) {
            "string" => strlen($this->pool[$key]->data),
            "array" => count($this->pool[$key]->data),
        };
    }

    /**
     * Append the data to the buffer of a sub-watcher with $id.
     * @param mixed $id
     * @param string|array|null $data
     * @return bool
     */
    public function appendData(mixed $id, string|array|null $data): bool
    {
        $key = static::key($id);

        if (!isset($this->pool[$key])) {
            return false;
        }

        $this->pool[$key]->data = match (gettype($this->pool[$key]->data)) {
            "string" => $this->pool[$key]->data . $data,
            "array" => array_merge($this->pool[$key]->data, $data),
        };

        return true;
    }

    /**
     * Extract a part of the buffer of a sub-watcher with $id.
     * @param mixed $id
     * @param int $offset
     * @param int|null $length
     * @return bool|string|array|null
     */
    public function sliceData(mixed $id, int $offset, int $length = null): bool|string|array|null
    {
        $key = static::key($id);

        if (!isset($this->pool[$key])) {
            return false;
        }

        switch (gettype($this->pool[$key]->data)) {
            case "string":
                $data = substr($this->pool[$key]->data, $offset, $length);
                $this->pool[$key]->data = substr($this->pool[$key]->data, strlen($data) + 1);
                return $data;
            case "array":
                $data = array_slice($this->pool[$key]->data, $offset, $length);
                $this->pool[$key]->data = array_slice($this->pool[$key]->data, count($data) + 1);
                return $data;
            case "NULL":
                return null;
        }

        return false;
    }
}
