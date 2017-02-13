<?php
namespace CelestialDemo\ViewHelper\Handlebars;

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

	public function ifNotEqual($a, $b, $context)
	{
		$args = func_get_args();
		$context = $args[count($args) - 1];

		if ((float) $a !== (float) $b) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}

	public function ifLessThan($greaterValue, $lesserValue, $context)
	{
		if ($lesserValue < $greaterValue) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}

	public function ifNotLessThan($lesserValue, $greaterValue, $context)
	{
		if ($greaterValue >= $lesserValue) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}

	public function ifGreaterThan($lesserValue, $greaterValue, $context)
	{
		if ($greaterValue > $lesserValue) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}

	public function ifNotGreaterThan($greaterValue, $lesserValue, $context)
	{
		if ($lesserValue <= $greaterValue) {
			$output = isset($context['fn']) ? $context['fn']($this) : '';
		} else {
			$output = isset($context['inverse']) ? $context['inverse']($this) : '';
		}

		return $output;
	}
}
