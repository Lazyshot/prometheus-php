<?php
namespace Prometheus;

class Client {
	private $registry;

	public function __construct(array $options = []) {
		$this->registry = new Registry;

		// TODO: Set memcache configuration

		// TODO: Allow option for requiring http basic authentication
	}

	public function newCounter(array $opts = []) {
		return $this->register(new Counter($opts));
	}

	private function register(Metric $metric) {
		return $this->registry->register($metric);
	}

	public function renderStats() {
		header("Content-Type: text/plain; version=0.0.4");

		foreach ($this->registry->getMetrics() as $metric) {
			echo $metric->serialize() . "\n";
		}
	}
}
