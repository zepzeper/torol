<?php

namespace Torol\Support;

class Stats
{
    public int $rowLoaded = 0;
    public float $durationInSeconds = 0;
    public float $peakMemoryUsageMb = 0;
    private float $startTime;

    public function __construct() {}

    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->rowLoaded = 0;
    }

    public function incrementRowsProcessed(): void
    {
        $this->rowLoaded++;
    }

    public function stop(): void
    {
        $endTime = microtime(true);

        $this->durationInSeconds = round($endTime - $this->startTime, 4);
        $this->peakMemoryUsageMb = round(memory_get_peak_usage(true) / 1024 / 1024, 4);
    }
}
