<?php
namespace Celestial\Module\Validation\Validator\Number;

use Celestial\Exception\InvalidConfigurationException;
use Celestial\Module\Validation\Base\AbstractValidator;

class MaximumDigitsValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$optionsValidation = $this->validateOptions($options);

		if (!$optionsValidation->isValid()) {
			throw new InvalidConfigurationException($optionsValidation->getErrors()->getByIndex(0)->getMessage());
		}

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

	public function validateOptions(array $options)
	{
		$result = $this->buildResult();

		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				$error = $this->buildError('Invalid value given for `negate` option in Number\MaximumDigitsValidator.');
				$result->pushError($error);
			}
		}

		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_int($options['compareTo'])) {
				$error = $this->buildError('Invalid value given for `compareTo` option in Number\MaximumDigitsValidator.');
				$result->pushError($error);
			}
		}

		return $result;
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('negate', $options)) {
			$options['negate'] = false;
		}

		return $options;
	}
}
