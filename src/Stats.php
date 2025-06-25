<?php

namespace Torol;

class Stats
{
	public function __construct(
		public readonly int $rowLoaded,
		public readonly float $durationInSeconds,
		public readonly float $peakMemoryUsageMb,
	)
	{
	}
}
