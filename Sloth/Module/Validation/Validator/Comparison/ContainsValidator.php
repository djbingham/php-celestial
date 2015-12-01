<?php
namespace Sloth\Module\Validation\Validator\Comparison;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class ContainsValidator implements ValidatorInterface
{
	public function validate($values, array $options = array())
	{
		$this->validateValues($values);
		$this->validateOptions($options);

		$values = $this->padValues($values);
		$options = $this->padOptions($options);

		$isValid = false;

		if ($options['strict'] === true) {
			foreach ($values['haystack'] as $comparisonValue) {
				if ($values['needle'] === $comparisonValue) {
					$isValid = true;
					break;
				}
			}
		} else {
			foreach ($values['haystack'] as $comparisonValue) {
				if ($values['needle'] == $comparisonValue) {
					$isValid = true;
					break;
				}
			}
		}

		if ($options['negate'] === true) {
			$isValid = !$isValid;
		}

		return $isValid;
	}

	private function validateValues($values)
	{
		if (!is_array($values)) {
			throw new InvalidArgumentException('Invalid values given to ContainsValidator. Must be an array.');
		}

		if (!array_key_exists('needle', $values)) {
			throw new InvalidArgumentException('No "needle" value found in values given to ContainsValidator.');
		}

		if (array_key_exists('haystack', $values) && count($values['haystack']) === 0) {
			throw new InvalidArgumentException('No "haystack" found in values given to ContainsValidator.');
		}
	}

	private function validateOptions(array $options)
	{
		if (array_key_exists('strict', $options)) {
			if (!is_bool($options['strict'])) {
				throw new InvalidArgumentException('Invalid value given for `strict` option in ContainsValidator.');
			}
		}

		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				throw new InvalidArgumentException('Invalid value given for `negate` option in ContainsValidator.');
			}
		}
	}

	private function padValues(array $values) {
		if (!array_key_exists('haystack', $values)) {
			$values['haystack'] = array();
		}

		return $values;
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('strict', $options)) {
			$options['strict'] = false;
		}

		if (!array_key_exists('negate', $options)) {
			$options['negate'] = false;
		}

		return $options;
	}
}
