<?php
namespace Sloth\Module\Validation\Validator;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class NumberValidator implements ValidatorInterface
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		switch ($options['decimalPlaces']) {
			case null:
				$isValid = is_numeric($value);
				break;
			case 0:
				$isValid = is_int($value);
				break;
			default:
				$formattedValue = number_format($value, $options['decimalPlaces']);
				$isValid = is_float($value) && (string)$value === $formattedValue;
				break;
		}

		return $isValid;
	}

	private function validateOptions(array $options)
	{
		if (array_key_exists('decimalPlaces', $options)) {
			if (!is_null($options['decimalPlaces']) && !is_int($options['decimalPlaces'])) {
				throw new InvalidArgumentException('Invalid value given for `decimalPlaces` option in NumberValidator');
			}
		}
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('decimalPlaces', $options)) {
			$options['decimalPlaces'] = null;
		}

		return $options;
	}
}
