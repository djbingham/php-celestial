<?php
namespace Sloth\Module\Validation\Validator\Comparison;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class EqualValidator extends AbstractValidator
{
	public function validate($values, array $options = array())
	{
		$this->validateValues($values);
		$this->validateOptions($options);
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
		if (!is_array($values)) {
			throw new InvalidArgumentException('Invalid values given to Comparison\EqualValidator. Must be an array.');
		}

		if (count($values) < 2) {
			throw new InvalidArgumentException('Insufficient values given to Comparison\EqualValidator. Requires at least two.');
		}
	}

	public function validateOptions(array $options)
	{
		if (array_key_exists('strict', $options)) {
			if (!is_bool($options['strict'])) {
				throw new InvalidArgumentException('Invalid value given for `strict` option in Comparison\EqualValidator.');
			}
		}

		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				throw new InvalidArgumentException('Invalid value given for `negate` option in Comparison\EqualValidator.');
			}
		}
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
