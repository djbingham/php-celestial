<?php
namespace Sloth\Module\Validation\Validator;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class TextValidator implements ValidatorInterface
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);

		$isValid = false;

		if (array_key_exists('minLength', $options)) {
			$isValid = $isValid && strlen($value) >= $options['minLength'];
		}

		if (array_key_exists('maxLength', $options)) {
			$isValid = $isValid && strlen($value) <= $options['maxLength'];
		}

		return $isValid;
	}

	private function validateOptions(array $options)
	{
		if (array_key_exists('minLength', $options)) {
			if (!is_null($options['minLength']) && !is_int($options['minLength'])) {
				throw new InvalidArgumentException('Invalid value given for `minLength` option in TextValidator');
			}
		}
		if (array_key_exists('maxLength', $options)) {
			if (!is_null($options['maxLength']) && !is_int($options['maxLength'])) {
				throw new InvalidArgumentException('Invalid value given for `maxLength` option in TextValidator');
			}
		}
	}
}
