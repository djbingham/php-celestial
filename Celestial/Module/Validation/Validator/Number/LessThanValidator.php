<?php
namespace Celestial\Module\Validation\Validator\Number;

use Celestial\Exception\InvalidConfigurationException;
use Celestial\Module\Validation\Base\AbstractValidator;

class LessThanValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$optionsValidation = $this->validateOptions($options);

		if (!$optionsValidation->isValid()) {
			throw new InvalidConfigurationException($optionsValidation->getErrors()->getByIndex(0)->getMessage());
		}

		$options = $this->padOptions($options);

		$valueIsLess = $value < $options['compareTo'];
		$error = null;

		if ($options['negate'] && $valueIsLess) {
			$error = $this->buildError(sprintf('`%s` is less than `%s`.', $value, $options['compareTo']));
		} elseif (!$options['negate'] && !$valueIsLess) {
			$error = $this->buildError(sprintf('`%s` is not less than `%s`.', $value, $options['compareTo']));
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
				$error = $this->buildError('Invalid value given for `negate` option in Number\LessThanValidator.');
				$result->pushError($error);
			}
		}

		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_int($options['compareTo'])) {
				$error = $this->buildError('Invalid value given for `compareTo` option in Number\LessThanValidator.');
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
