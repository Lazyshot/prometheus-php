<?php
namespace Prometheus;

use io\prometheus\client\Counter as PBCounter;
use io\prometheus\client\Gauge as PBGauge;
use io\prometheus\client\Untyped as PBUntyped;
use io\prometheus\client\LabelPair as PBLabelPair;
use io\prometheus\client\Metric as PBMetric;
use io\prometheus\client\MetricFamily as PBMetricFamily;
use io\prometheus\client\MetricType as PBMetricType;

abstract class Metric {
	private $values = [];
	private $labels = [];

	public $namespace;
	public $name;
	public $subsystem;
	public $help;

	public $full_name;

	public function __construct(array $options = []) {
		$this->name = $opts['name'] ?: '';
		$this->namespace = $opts['namespace'] ?: '';
		$this->subsystem = $opts['subsystem'] ?: '';
		$this->help = $opts['help'] ?: '';

		if (empty($this->name)) throw new PrometheusException("A name is required for a metric");
		if (empty($this->help)) throw new PrometheusException("A help is required for a metric");

		$this->full_name = implode('_', [$this->namespace, $this->subsystem, $this->name]);
	}

	public function values() {
		$values = [];
		foreach ($this->values as $hash => $val) {
			$values []= [$this->labels[$hash], $val];
		}

		return $values;
	}

	public function get(array $labels = []) {
		$hash = $this->hashLabels($labels);
		return $this->values[$hash] ?: $this->defaultValue();
	}

	public function defaultValue() {
		return null;
	}

	abstract public function type();

	public function toProto() {
		$mf = new PBMetricFamily();
		$mf->setName($this->full_name);
		$mf->setHelp($this->help);

		switch ($this->type()) {
			case "counter":
				$type = PBMetricType::COUNTER;
				$metricClass = PBCounter;
				break;
			case "gauge":
				$type = PBMetricType::GAUGE;
				$metricClass = PBGauge;
				break;
			default:
				$type = PBMetricType::UNTYPED;
				$metricClass = PBUntyped;
		}

		$mf->setType($type);

		foreach ($this->values() as $val) {
			list($labels, $value) = $val;
			$label_pairs = [];

			foreach ($labels as $k => $v) {
				$label_pair = new PBLabelPair;
				$label_pair->setName($k);
				$label_pair->setValue($v);
				$label_pairs []= $label_pair;
			}

			$metric = new PBMetric();
			$metric->setLabelList($label_pairs);

			$metric_sub = new $metricClass();
			$metric_sub->setValue($value);

			switch ($this->type()) {
				case "counter":
					$metric->setCounter($metric_sub);
					break;
				case "gauge":
					$metric->setGauge($metric_sub);
					break;
				default:
					$metric->setUntyped($metric_sub);
			}

			$mf->addMetric($metric);
		}

		return $mf;
	}

	protected function hashLabels(array $labels = []) {
		$hash = md5(json_encode($labels, JSON_FORCE_OBJECT));
		$this->labels[$hash] = $labels;
		// TODO: save to memcached

		return $hash;
	}
}
