<?php

namespace NSCL;

class IteratorImplementation implements \Iterator
{
    protected $innerValues = [0, 1, 2, 3, 4, 5];

    protected $currentIndex = 0;
    protected $lastIndex = 5;

    public function rewind()
    {
        $this->currentIndex = 0;
    }

    public function valid()
    {
        return $this->currentIndex <= $this->lastIndex;
    }

    public function current()
    {
        if ($this->currentIndex <= $this->lastIndex) {
            return $this->innerValues[$this->currentIndex];
        } else {
            return null;
        }
    }

    public function next()
    {
        $this->currentIndex++;
    }

    public function key()
    {
        return $this->currentIndex;
    }

    public function addItem($newValue)
    {
        $this->innerValues[] = $newValue;

        $this->lastIndex++;

        // Don't make invalid index valid after adding the new item
        if ($this->currentIndex == $this->lastIndex) {
            $this->currentIndex++;
        }
    }

    public function removeItemAt($index)
    {
        if ($index >= 0 && $index <= $this->lastIndex) {
            array_splice($this->innerValues, $index, 1);

            $this->lastIndex--;

            if ($index <= $this->currentIndex) {
                $this->currentIndex--;
            }
        }
    }
}
