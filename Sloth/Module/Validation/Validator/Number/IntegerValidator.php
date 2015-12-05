<?php
namespace Sloth\Module\Validation\Validator\Number;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class IntegerValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		$isInteger = (floatval($value) == intval($value));
		$error = null;

		if ($options['negate'] === true && $isInteger) {
			$error = $this->buildError(sprintf('`%s` is an integer.', $value));
		} elseif (!$options['negate'] && !$isInteger) {
			$error = $this->buildError(sprintf('`%s` is not an integer.', $value));
		}

		$result = $this->buildResult();

		if ($error !== null) {
			$result->pushError($error);
		}

		return $result;
	}

	private function validateOptions(array $options)
	{
		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				throw new InvalidArgumentException('Invalid value given for `negate` option in Number\IntegerValidator.');
			}
		}
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('negate', $options)) {
			$options['negate'] = false;
		}

		return $options;
	}
}
