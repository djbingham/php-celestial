<?php
namespace Celestial\Module\Validation\Validator\Number;

use Celestial\Exception\InvalidConfigurationException;
use Celestial\Module\Validation\Base\AbstractValidator;

class NumberValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$optionsValidation = $this->validateOptions($options);

		if (!$optionsValidation->isValid()) {
			throw new InvalidConfigurationException($optionsValidation->getErrors()->getByIndex(0)->getMessage());
		}

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

	public function validateOptions(array $options)
	{
		$result = $this->buildResult();

		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				$error = $this->buildError('Invalid value given for `negate` option in Number\IsNumberValidator.');
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
