<?php
namespace Sloth\Module\Validation\Validator\Number;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class NumberValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		$isNumeric = is_numeric($value);
		$error = null;

		if ($options['negate'] === true && $isNumeric) {
			$error = $this->buildError(sprintf('`%s` is a number.', $value));
		} elseif (!$options['negate'] && !$isNumeric) {
			$error = $this->buildError(sprintf('`%s` is not a number.', $value));
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
				throw new InvalidArgumentException('Invalid value given for `negate` option in Number\IsNumberValidator.');
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
