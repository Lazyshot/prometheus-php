<?php

namespace Prometheus;

class Client
{
	private $registry;
	private $base_uri;

	public function __construct(string $base_uri = null)
	{
		$this->registry = new Registry;

		if (null === $base_uri)
			throw new PrometheusException("Prometheus requires a base_uri option, which points to the pushgateway");

		$this->base_uri = $base_uri;

		// TODO: Allow option for requiring http basic authentication
	}

	public function newCounter(array $opts = []) : Counter
	{
		return $this->register(new Counter($opts));
	}

	public function newGauge(array $opts = []) : Gauge
	{
		return $this->register(new Gauge($opts));
	}

	public function newHistogram(array $opts = []) : Histogram
	{
		return $this->register(new Histogram($opts));
	}

	private function register(Metric $metric) : Metric
	{
		return $this->registry->register($metric);
	}

	public function getMetric($metric) : ?Metric
	{
		return $this->registry->getMetric($metric);
	}

	public function serialize() : string
	{

		$body = "";

		foreach ($this->registry->getMetrics() as $metric) {
			$body .= $metric->serialize() . "\n";
		}

		return $body;
	}


	function pushMetrics(string $job = null, string $instance = null)
	{
		$url = $this->base_uri;

		if ($instance && !$job) throw new PrometheusException("Instance passed but job was set to null.  Job must be set to a non empty string.");
		if (!is_null($job) && $job == "") throw new PrometheusException("Job was set to an empty string.  Job must be set to a non empty string.");
		elseif (!is_null($instance) && $instance == "") throw new PrometheusException("Instance was set to an empty string.  If Instance is set it must be a non empty string.");

		if ($job) $url .= $job;
		if ($instance) $url .= "/instance/" . $instance;

		$ch = \curl_init($url);

		\curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		\curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		\curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		\curl_setopt($ch, CURLOPT_POSTFIELDS, $this->serialize());

		if (\curl_exec($ch) === false) {
			throw new PrometheusException("Error sending metrics to push gateway: " . \curl_error($ch));
		}

		$this->registry->cleanup();

		#TODO: Can the pushgateway return a 200 on successful PUT?
		# Currently it returns nothing no matter what, lame
	}
}

