<?php
namespace Sloth\Module\Validation\Validator\Number;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class MaxDecimalPlacesValidator implements ValidatorInterface
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);

		$regex = sprintf('/^\d+(\.\d{1,%s})?$/', $options['compareTo']);

		return preg_match($regex, (string)$value);
	}

	private function validateOptions(array $options)
	{
		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_int($options['compareTo'])) {
				throw new InvalidArgumentException(
						'Invalid value given for `compareTo` option in Number\MaxDecimalPlacesValidator'
				);
			}
		}
	}
}
