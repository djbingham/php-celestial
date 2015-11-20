<?php
namespace Sloth\Module\Validation\Validator\Number;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class NumberValidator implements ValidatorInterface
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		if ($options['compareTo'] === true) {
			$isValid = is_numeric($value);
		} else {
			$isValid = !is_numeric($value);
		}

		return $isValid;
	}

	private function validateOptions(array $options)
	{
		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_bool($options['compareTo'])) {
				throw new InvalidArgumentException('Invalid value given for `compareTo` option in Number\IsNumberValidator');
			}
		}
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('compareTo', $options)) {
			$options['compareTo'] = true;
		}
		return $options;
	}
}
