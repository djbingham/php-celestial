<?php
namespace Sloth\Module\Validation\Validator\Number;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class MaximumDigitsValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		$digitCount = strlen(str_replace('.', '', $value));
		$tooManyDigits = $digitCount > $options['compareTo'];
		$error = null;

		if ($options['negate'] && !$tooManyDigits) {
			$error = $this->buildError(sprintf('`%s` has less than or equal to `%s` digits.', $value, $options['compareTo']));
		} elseif (!$options['negate'] && $tooManyDigits) {
			$error = $this->buildError(sprintf('`%s` has more than `%s` digits.', $value, $options['compareTo']));
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
				throw new InvalidArgumentException('Invalid value given for `negate` option in Number\MaximumDigitsValidator.');
			}
		}

		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_int($options['compareTo'])) {
				throw new InvalidArgumentException(
					'Invalid value given for `compareTo` option in Number\MaximumDigitsValidator'
				);
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
