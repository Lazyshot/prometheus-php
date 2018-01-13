<?php

namespace Prometheus;

class Histogram extends Metric
{
	public function __construct(array $opts = [])
	{
		parent::__construct($opts);
		$this->buckets = isset($opts['buckets']) ? $opts['buckets'] : [1, 2, 3];
	}

	public function type() : string
	{
		return "histogram";
	}

	public function defaultValue() : int
	{
		return 0;
	}

	public function getBuckets() : array
	{
		return $this->buckets;
	}

	public function observe(array $labels, float $value) : void
	{
		$labels["__suffix"] = "_bucket";
		foreach ($this->buckets as $bucket) {
			$labels["le"] = $bucket;
			$hash         = $this->hashLabels($labels);
			if (!isset($this->values[$hash])) {
				$this->values[$hash] = $this->defaultValue();
			}
			if ($value <= $bucket) {
				$this->values[$hash] += 1;
			}
		}
		$labels["le"] = '+Inf';
		$hash         = $this->hashLabels($labels);
		if (!isset($this->values[$hash])) {
			$this->values[$hash] = $this->defaultValue();
		}
		$this->values[$hash] += 1;
		unset($labels["le"]);


		$labels["__suffix"] = "_count";
		$hash               = $this->hashLabels($labels);
		if (!isset($this->values[$hash])) {
			$this->values[$hash] = $this->defaultValue();
		}
		$this->values[$hash] += 1;


		$labels["__suffix"] = "_sum";
		$hash               = $this->hashLabels($labels);
		if (!isset($this->values[$hash])) {
			$this->values[$hash] = $this->defaultValue();
		}
		$this->values[$hash] += $value;
	}
}
