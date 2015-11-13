<?php
namespace Sloth\Module\Validation\Validator\Comparison;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class EqualValidator implements ValidatorInterface
{
	public function validate($values, array $options = array())
	{
		$this->validateValues($values);
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		$isValid = true;
		$referenceValue = array_shift($values);

		foreach ($values as $value) {
			if ($options['strict'] === true && $value !== $referenceValue) {
				$isValid = false;
				break;
			} elseif ($value != $referenceValue) {
				$isValid = false;
				break;
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
			throw new InvalidArgumentException('Invalid values given to EqualValidator. Must be an array.');
		}

		if (count($values) < 2) {
			throw new InvalidArgumentException('Insufficient values given to EqualValidator. Requires at least two.');
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
