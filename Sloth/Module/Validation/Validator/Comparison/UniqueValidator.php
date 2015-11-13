<?php
namespace Sloth\Module\Validation\Validator\Comparison;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class UniqueValidator implements ValidatorInterface
{
	public function validate($values, array $options = array())
	{
		$this->validateValues($values);
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		$isValid = false;

		if (count(array_unique($values)) === count($values)) {
			$isValid = true;
		}

		if ($options['negate'] === true) {
			$isValid = !$isValid;
		}

		return $isValid;
	}

	private function validateValues($values)
	{
		if (!is_array($values)) {
			throw new InvalidArgumentException('Invalid values given to UniqueValidator. Must be an array.');
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
