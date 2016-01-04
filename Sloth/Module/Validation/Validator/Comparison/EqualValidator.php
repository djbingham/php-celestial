<?php
namespace Sloth\Module\Validation\Validator\Comparison;

use Sloth\Exception\InvalidConfigurationException;
use Sloth\Module\Validation\Base\AbstractValidator;

class EqualValidator extends AbstractValidator
{
	public function validate($values, array $options = array())
	{
		$valuesValidation = $this->validateValues($values);
		$optionsValidation = $this->validateOptions($options);

		if (!$valuesValidation->isValid()) {
			throw new InvalidConfigurationException($optionsValidation->getErrors()->getByIndex(0)->getMessage());
		}

		if (!$optionsValidation->isValid()) {
			throw new InvalidConfigurationException($optionsValidation->getErrors()->getByIndex(0)->getMessage());
		}

		$options = $this->padOptions($options);

		$referenceValue = array_shift($values);

		$result = $this->buildResult();

		foreach ($values as $value) {
			$error = null;
			$valuesAreEqual = $this->valuesAreEqual($value, $referenceValue, $options['strict']);

			if ($options['negate'] && $valuesAreEqual) {
				$error = $this->buildError(sprintf('`%s` is equal to `%s`.', $value, $referenceValue));
			} elseif (!$options['negate'] && !$valuesAreEqual) {
				$error = $this->buildError(sprintf('`%s` is not equal to `%s`.', $value, $referenceValue));
			}

			if ($error !== null) {
				$result->pushError($error);
			}
		}

		return $result;
	}

	private function valuesAreEqual($firstValue, $secondValue, $strictComparison = false)
	{
		if ($strictComparison) {
			return $firstValue === $secondValue;
		} else {
			return $firstValue == $secondValue;
		}
	}

	private function validateValues($values)
	{
		$result = $this->buildResult();

		if (!is_array($values)) {
			$error = $this->buildError('Invalid values given to Comparison\EqualValidator. Must be an array.');
			$result->pushError($error);
		}

		if (count($values) < 2) {
			$error = $this->buildError('Insufficient values given to Comparison\EqualValidator. Requires at least two.');
			$result->pushError($error);
		}

		return $result;
	}

	public function validateOptions(array $options)
	{
		$result = $this->buildResult();

		if (array_key_exists('strict', $options)) {
			if (!is_bool($options['strict'])) {
				$error = $this->buildError('Invalid value given for `strict` option in Comparison\EqualValidator.');
				$result->pushError($error);
			}
		}

		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				$error = $this->buildError('Invalid value given for `negate` option in Comparison\EqualValidator.');
				$result->pushError($error);
			}
		}

		return $result;
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('strict', $options)) {
			$options['strict'] = false;
		}

		if (!array_key_exists('negate', $options)) {
			$options['negate'] = false;
		}

		return $options;
	}
}
