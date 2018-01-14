<?php

namespace Prometheus;

class Gauge extends Metric
{
	public function __construct(array $opts = [])
	{
		parent::__construct($opts);
	}

	public function type() : string
	{
		return "gauge";
	}

	public function defaultValue() : int
	{
		return 0;
	}

	public function set(array $labels, float $val) : self
	{
		$hash = $this->hashLabels($labels);
		if (!isset($this->values[$hash]))
			$this->values[$hash] = $this->defaultValue();

		$this->values[$hash] = $val;

		return $this;
	}
}
