<?php
declare(strict_types=1);

namespace Liquid\Framework\Config;

class FileIterator implements \Iterator, \Countable
{
    public function __construct(private array $paths)
    {
    }

    /**
     * Rewind
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->paths);
    }

    /**
     * Current
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->paths);
//        $fileRead = new Read(new FileType(), $this->key());
//        return $fileRead->readAll();
    }

    /**
     * Key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return current($this->paths);
    }

    /**
     * Next
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->paths);
    }

    /**
     * Valid
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return (bool)$this->key();
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function toArray()
    {
        $result = [];
        foreach ($this as $item) {
            $result[$this->key()] = $item;
        }
        return $result;
    }

    /**
     * Count
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->paths);
    }
}

