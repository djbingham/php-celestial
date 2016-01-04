<?php
namespace Sloth\Module\Validation\Validator\Comparison;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class ContainsValidator extends AbstractValidator
{
	public function validate($values, array $options = array())
	{
		$this->validateValues($values);
		$this->validateOptions($options);

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
		if (!is_array($values)) {
			throw new InvalidArgumentException('Invalid values given to ContainsValidator. Must be an array.');
		}

		if (!array_key_exists('needle', $values)) {
			throw new InvalidArgumentException('No "needle" value found in values given to ContainsValidator.');
		}

		if (array_key_exists('haystack', $values) && count($values['haystack']) === 0) {
			throw new InvalidArgumentException('No "haystack" found in values given to ContainsValidator.');
		}
	}

	public function validateOptions(array $options)
	{
		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				throw new InvalidArgumentException('Invalid value given for `negate` option in ContainsValidator.');
			}
		}

		if (array_key_exists('strict', $options)) {
			if (!is_bool($options['strict'])) {
				throw new InvalidArgumentException('Invalid value given for `strict` option in ContainsValidator.');
			}
		}
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
