<?php
namespace SlothDemo\ViewHelper;

class MathComparison
{
	public function ifEqual($a, $b, $context)
	{
		$args = func_get_args();
		$context = $args[count($args) - 1];

		if ((float) $a === (float) $b) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}

	public function ifLessThan($lesserValue, $greaterValue, $context)
	{
		if ($lesserValue < $greaterValue) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}

	public function ifLessThanOrEqual($lesserValue, $greaterValue, $context)
	{
		if ($lesserValue <= $greaterValue) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}

	public function ifGreaterThan($greaterValue, $lesserValue, $context)
	{
		if ($greaterValue > $lesserValue) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}

	public function ifGreaterThanOrEqual($greaterValue, $lesserValue, $context)
	{
		if ($greaterValue >= $lesserValue) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}
}
