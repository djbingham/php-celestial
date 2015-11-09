<?php
namespace Sloth\Module\Validation\Validator\Text;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class IsTextValidator implements ValidatorInterface
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		if ($options['compareTo'] === false) {
			$isValid = is_string($value);
		} else {
			$isValid = !is_string($value);
		}

		return $isValid;
	}

	private function validateOptions(array $options)
	{
		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_int($options['compareTo'])) {
				throw new InvalidArgumentException('Invalid value given for `compareTo` option in Text\IsTextValidator');
			}
		}
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('compareTo', $options)) {
			$options['compareTo'] = false;
		}
		return $options;
	}
}
