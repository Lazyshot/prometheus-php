<?php
namespace Prometheus;

require_once 'protos/metrics.php';

use DrSlump\Protobuf\Codec\Binary\Writer;

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
		$w = new Writer;

		foreach ($this->registry->getMetrics() as $metric) {
			$metricProto = $metric->toProto();

			$buf = $metricProto->serialize();
			$len = strlen($buf);

			$w->varint($len);
			$w->write($buf);
		}

		header("Content-Type: application/vnd.google.protobuf; proto=io.prometheus.client.MetricFamily; encoding=delimited");

		fwrite(STDOUT, $w->getBytes());
	}
}
