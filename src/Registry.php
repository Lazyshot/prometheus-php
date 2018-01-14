<?php

namespace Prometheus;


class Registry
{
	private $metrics = [];

	public function register(Metric $metric)
	{
		$name = $metric->full_name;

		if (isset($this->metrics[$name])) {
			throw new PrometheusException("Metric name must be unique");
		}

		$this->metrics[$name] = $metric;

		return $metric;
	}

	public function cleanup()
	{
		$this->metrics = [];
	}

	public function getMetric($metric) : ?Metric
	{
		return $this->metrics[$metric] ?? null;
	}

	public function getMetrics() : array
	{
		return $this->metrics;
	}
}
