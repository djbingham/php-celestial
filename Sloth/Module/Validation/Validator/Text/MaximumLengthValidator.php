<?php
namespace Sloth\Module\Validation\Validator\Text;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class MaximumLengthValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		$longerThanMaximum = strlen($value) > $options['compareTo'];
		$error = null;

		if ($options['negate'] && !$longerThanMaximum) {
			$error = $this->buildError(sprintf('`%s` is not longer than `%s`.', $value, $options['compareTo']));
		} elseif (!$options['negate'] && $longerThanMaximum) {
			$error = $this->buildError(sprintf('`%s` is longer than `%s`.', $value, $options['compareTo']));
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
				throw new InvalidArgumentException('Invalid value given for `negate` option in Text\MaximumLengthValidator.');
			}
		}

		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_int($options['compareTo'])) {
				throw new InvalidArgumentException('Invalid value given for `compareTo` option in Text\MaximumLengthValidator');
			}
		}
	}

	private function padOptions(array $options)
	{
		if (!array_key_exists('negate', $options)) {
			$options['negate'] = false;
		}

		if (!array_key_exists('compareTo', $options)) {
			$options['compareTo'] = 0;
		}

		return $options;
	}
}
