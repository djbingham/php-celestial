<?php
namespace Celestial\Module\Validation\Validator\Comparison;

use Celestial\Exception\InvalidConfigurationException;
use Celestial\Module\Validation\Base\AbstractValidator;

class ContainsValidator extends AbstractValidator
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

		$values = $this->padValues($values);
		$options = $this->padOptions($options);

		$needle = $values['needle'];
		$haystack = $values['haystack'];

		$needleFound = false;

		if ($options['strict'] === true) {
			foreach ($haystack as $comparisonValue) {
				if ($needle === $comparisonValue) {
					$needleFound = true;
					break;
				}
			}
		} else {
			foreach ($haystack as $comparisonValue) {
				if ($needle == $comparisonValue) {
					$needleFound = true;
					break;
				}
			}
		}

		$error = null;

		if ($options['negate'] && $needleFound === true) {
			$error = $this->buildError(
				sprintf('Unexpected needle found in haystack. Needle: %s. Haystack: %s.', $needle, json_encode($haystack))
			);
		} else if (!$options['negate'] && !$needleFound) {
			$error = $this->buildError(
				sprintf('Expected needle not found in haystack. Needle: %s. Haystack: %s.', $needle, json_encode($haystack))
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
			$error = $this->buildError('Invalid values given to ContainsValidator. Must be an array.');
			$result->pushError($error);
		}

		if (!array_key_exists('needle', $values)) {
			$error = $this->buildError('No "needle" value found in values given to ContainsValidator.');
			$result->pushError($error);
		}

		if (array_key_exists('haystack', $values) && count($values['haystack']) === 0) {
			$error = $this->buildError('No "haystack" found in values given to ContainsValidator.');
			$result->pushError($error);
		}

		return $result;
	}

	public function validateOptions(array $options)
	{
		$result = $this->buildResult();

		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				$error = $this->buildError('Invalid value given for `negate` option in ContainsValidator.');
				$result->pushError($error);
			}
		}

		if (array_key_exists('strict', $options)) {
			if (!is_bool($options['strict'])) {
				$error = $this->buildError('Invalid value given for `strict` option in ContainsValidator.');
				$result->pushError($error);
			}
		}

		return $result;
	}

	private function padValues(array $values) {
		if (!array_key_exists('haystack', $values)) {
			$values['haystack'] = array();
		}

		return $values;
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('negate', $options)) {
			$options['negate'] = false;
		}

		if (!array_key_exists('strict', $options)) {
			$options['strict'] = false;
		}

		return $options;
	}
}
