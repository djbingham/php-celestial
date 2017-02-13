<?php
namespace Celestial\Module\Validation\Validator\Text;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Exception\InvalidConfigurationException;
use Celestial\Module\Data\TableValidation\Exception\InvalidTableException;
use Celestial\Module\Validation\Base\AbstractValidator;

class TextValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$optionsValidation = $this->validateOptions($options);

		if (!$optionsValidation->isValid()) {
			throw new InvalidConfigurationException($optionsValidation->getErrors()->getByIndex(0)->getMessage());
		}

		$options = $this->padOptions($options);

		$isText = is_string($value);
		$error = null;

		if ($options['negate'] === true && $isText) {
			$error = $this->buildError(sprintf('`%s` is text.', $value));
		} elseif (!$options['negate'] && !$isText) {
			$error = $this->buildError(sprintf('`%s` is not text.', $value));
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
				$error = $this->buildError('Invalid value given for `negate` option in Text\IsTextValidator.');
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
