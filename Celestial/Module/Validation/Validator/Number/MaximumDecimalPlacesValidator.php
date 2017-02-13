<?php
namespace Celestial\Module\Validation\Validator\Number;

use Celestial\Exception\InvalidConfigurationException;
use Celestial\Module\Validation\Base\AbstractValidator;

class MaximumDecimalPlacesValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$optionsValidation = $this->validateOptions($options);

		if (!$optionsValidation->isValid()) {
			throw new InvalidConfigurationException($optionsValidation->getErrors()->getByIndex(0)->getMessage());
		}

		$options = $this->padOptions($options);

		$decimalsCount = $this->countDecimals($value);
		$hasTooManyDecimals = ($decimalsCount > $options['compareTo']);

		$error = null;

		if ($options['negate'] && !$hasTooManyDecimals) {
			$error = $this->buildError(sprintf('`%s` has less than or equal to `%s` decimal places.', $value, $options['compareTo']));
		} elseif (!$options['negate'] && $hasTooManyDecimals) {
			$error = $this->buildError(sprintf('`%s` has more than `%s` decimal places.', $value, $options['compareTo']));
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
				$error = $this->buildError('Invalid value given for `negate` option in Number\MaxDecimalPlacesValidator.');
				$result->pushError($error);
			}
		}

		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_int($options['compareTo'])) {
				$error = $this->buildError('Invalid value given for `compareTo` option in Number\MaxDecimalPlacesValidator.');
				$result->pushError($error);
			}
		}

		return $result;
	}

	private function countDecimals($number)
	{
		$decimalNumber = $number - floor($number);

		/*
			Incrementally shift the decimal place to the right until the shifted number has no decimals left.
			The number of places shifted must be the number of decimal places in the original number.
		*/
		for ($decimals = 0; ceil($decimalNumber); $decimals++) {
			$shiftedNumber = $number * pow(10, $decimals + 1);
			$decimalNumber = $shiftedNumber - floor($shiftedNumber);
		}

		return $decimals;
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('negate', $options)) {
			$options['negate'] = false;
		}

		return $options;
	}
}
