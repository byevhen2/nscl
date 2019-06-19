<?php

declare(strict_types = 1);

namespace sid;

/**
 * Record failures like: 2, 4, 10-12, 17-43.
 */
class FailuresRecorder
{
    const STATUS_FAILED     = 0;
    const STATUS_SUCCEED    = 1;
    const STATUS_UNASSIGNED = 2;

    protected $failures = []; // ["2", "4", "10-12", "17-43"]
    protected $failuresCount = 0; // 32

    protected $firstFailId = 0;
    protected $lastFailId  = 0;

    protected $currentId = 1;
    protected $currentStatus = self::STATUS_UNASSIGNED;

    public function __construct(int $startId)
    {
        $this->currentId = $startId;
    }

    public function nextSucceed()
    {
        switch ($this->currentStatus) {
            case self::STATUS_FAILED:
                if ($this->lastFailId == $this->firstFailId) {
                    $this->failures[] = $this->lastFailId;
                } else {
                    $this->failures[] = $this->firstFailId . '-' . $this->lastFailId;
                }
                break;

            case self::STATUS_SUCCEED:
                break;
        }

        $this->currentId++;
        $this->currentStatus = self::STATUS_SUCCEED;
    }

    public function nextFailed()
    {
        $this->lastFailId = $this->currentId;

        switch ($this->currentStatus) {
            case self::STATUS_SUCCEED:
            case self::STATUS_UNASSIGNED:
                $this->firstFailId = $this->currentId;
                break;
        }

        $this->failuresCount++;
        $this->currentId++;
        $this->currentStatus = self::STATUS_FAILED;
    }

    public function finish()
    {
        $this->nextSucceed();
    }

    public function getFailures(): array
    {
        return $this->failures;
    }

    public function getFailuresCount(): int
    {
        return $this->failuresCount;
    }

    public function hasFailures(): bool
    {
        return !empty($this->failures);
    }

    public function __toString(): string
    {
        if ($this->hasFailures()) {
            return implode(', ', $this->failures) . " ({$this->failuresCount} in total)";
        } else {
            return 'none';
        }
    }
}
