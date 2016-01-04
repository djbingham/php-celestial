<?php
namespace Sloth\Module\Validation\Validator\Comparison;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class UniqueValidator extends AbstractValidator
{
	public function validate($values, array $options = array())
	{
		$this->validateValues($values);
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		$valuesAreUnique = count(array_unique($values)) === count($values);
		$error = null;

		if ($options['negate'] && $valuesAreUnique) {
			$error = $this->buildError(
				sprintf('Expected non-unique list, but all values are unique: %s.', json_encode($values))
			);
		} elseif (!$options['negate'] && !$valuesAreUnique) {
			$error = $this->buildError(
				sprintf('Expected unique list, but non-unique values found: %s.', json_encode($values))
			);
		}

		$result = $this->buildResult();
		if ($error !== null) {
			$result->pushError($error);
		}

		return $result;
	}

	private function validateValues($values)
	{
		if (!is_array($values)) {
			throw new InvalidArgumentException('Invalid values given to Comparison\UniqueValidator. Must be an array.');
		}
	}

	public function validateOptions(array $options)
	{
		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				throw new InvalidArgumentException('Invalid value given for `negate` option in Comparison\UniqueValidator.');
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
