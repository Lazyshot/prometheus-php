<?php

namespace Prometheus;

class Counter extends Metric
{
	public function __construct(array $opts = [])
	{
		parent::__construct($opts);
	}

	public function type() : string
	{
		return "counter";
	}

	public function defaultValue() : int
	{
		return 0;
	}

	public function increment(array $labels = [], $by = 1) : int
	{
		$hash = $this->hashLabels($labels);

		if (!isset($this->values[$hash]))
			$this->values[$hash] = $this->defaultValue();

		$this->values[$hash] += $by;

		return $this->values[$hash];
	}

	public function decrement(array $labels = [], $by = 1) : int
	{
		$this->increment($labels, -1 * $by);
	}
}
