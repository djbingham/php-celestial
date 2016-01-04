<?php
namespace Sloth\Module\Validation\Validator\Comparison;

use Sloth\Exception\InvalidConfigurationException;
use Sloth\Module\Validation\Base\AbstractValidator;

class UniqueValidator extends AbstractValidator
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
		$result = $this->buildResult();

		if (!is_array($values)) {
			$error = $this->buildError('Invalid values given to Comparison\UniqueValidator. Must be an array.');
			$result->pushError($error);
		}

		return $result;
	}

	public function validateOptions(array $options)
	{
		$result = $this->buildResult();

		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				$error = $this->buildError('Invalid value given for `negate` option in Comparison\UniqueValidator.');
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
