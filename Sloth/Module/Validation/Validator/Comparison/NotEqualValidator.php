<?php
namespace Sloth\Module\Validation\Validator\Comparison;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class NotEqualValidator implements ValidatorInterface
{
	public function validate($values, array $options = array())
	{
		if (!is_array($values)) {

		}
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		if ($options['strict'] === true) {
			$isValid = $values[0] === $values[1];
		} else {
			$isValid = $values[0] == $values[1];
		}

		return $isValid;
	}

	private function validateOptions(array $options)
	{
		if (array_key_exists('strict', $options)) {
			if (!is_bool($options['strict'])) {
				throw new InvalidArgumentException('Invalid value given for `strict` option in NotEqualValidator');
			}
		}
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('strict', $options)) {
			$options['strict'] = false;
		}

		return $options;
	}
}
