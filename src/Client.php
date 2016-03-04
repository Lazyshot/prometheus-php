<?php
namespace Prometheus;

require_once(dirname(__FILE__) . '/PrometheusException.php');
require_once(dirname(__FILE__) . '/Metric.php');
require_once(dirname(__FILE__) . '/Counter.php');
require_once(dirname(__FILE__) . '/Gauge.php');
require_once(dirname(__FILE__) . '/Registry.php');
require_once(dirname(__FILE__) . '/Histogram.php');

class Client {
	private $registry;
	private $options;
	private $base_uri;

	public function __construct(array $options = []) {
		$this->registry = new Registry;

		$this->options = $options;


		if (empty($this->options['base_uri']))
			throw new PrometheusException("Prometheus requires a base_uri option, which points to the pushgateway");

		$this->base_uri = $options['base_uri'];

		// TODO: Allow option for requiring http basic authentication
	}

	public function newCounter(array $opts = []) {
		return $this->register(new Counter($opts));
	}

	public function newGauge(array $opts = []) {
		return $this->register(new Gauge($opts));
	}

	public function newHistogram(array $opts = []) {
		return $this->register(new Histogram($opts));
	}

	private function register(Metric $metric) {
		return $this->registry->register($metric);
	}

	public function serialize() {

		$body = "";

		foreach ($this->registry->getMetrics() as $metric) {
			$body .= $metric->serialize() . "\n";
		}

		return $body;
	}


	function pushMetrics($job=Null, $instance=Null)
	{
		$url = $this->base_uri;

		if($instance && !$job) throw new PrometheusException("Instance passed but job was set to null.  Job must be set to a non empty string.");
		if(!is_null($job) && $job == "" ) throw new PrometheusException("Job was set to an empty string.  Job must be set to a non empty string.");
		elseif(!is_null($instance) && $instance == "" ) throw new PrometheusException("Instance was set to an empty string.  If Instance is set it must be a non empty string.");

		if($job) $url.=$job;
		if($instance) $url.="/instance/".$instance;

		$ch = curl_init($url);

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->serialize() );

		curl_exec( $ch );

		#TODO: Can the pushgateway return a 200 on successful PUT?
		# Currently it returns nothing no matter what, lame
	}
}

