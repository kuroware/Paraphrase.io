<?php

abstract class TimeComplexity {
	public $function, $big_o_notation, $summary;

	public static $defaults = array(
		'function' => null,
		'big_o_notation' => null,
		'summary' => null
	);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		$this->function = (is_a($args['function'], 'LanguageFunction')) ? $args['function'] : null;
		$this->big_o_notation = $args['big_o_notation'];
		$this->summary = $args['summary'];
		$this->html_big_o_notation = $this->resolve_big_o_notation();
	}

	final private function resolve_big_o_notation() {
		if ($this->big_o_notation) {
			$big_o = $this->big_o_notation;
			if (strpos($this->big_o_notation, '^')) {
				$big_o = explode('^', $from_function->big_o_average_case);
				$big_o = $big_o[0] . '<sup>' . $big_o[1] . '</sup>';
			}
			return $big_o;
		}
		else {
			return $this->big_o_notation;
		}
	}
}


class BestCase extends TimeComplexity{

}


class WorstCase extends TimeComplexity {

}


class AverageCase extends TimeComplexity {
	
}