<?php
namespace Sloth\Module\Validation\Validator\Text;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class MinimumLengthValidator implements ValidatorInterface
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		return strlen($value) >= $options['compareTo'];
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
			$options['compareTo'] = 0;
		}
		return $options;
	}
}
