<?php
namespace Sloth\Module\Validation\Validator\Text;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class TextValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
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

	private function validateOptions(array $options)
	{
		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				throw new InvalidArgumentException('Invalid value given for `negate` option in Text\IsTextValidator.');
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
