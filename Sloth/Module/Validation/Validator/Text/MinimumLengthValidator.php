<?php
namespace Sloth\Module\Validation\Validator\Text;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class MinimumLengthValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		$shorterThanMinimum = strlen($value) < $options['compareTo'];
		$error = null;

		if ($options['negate'] && !$shorterThanMinimum) {
			$error = $this->buildError(sprintf('`%s` is not shorter than `%s`.', $value, $options['compareTo']));
		} elseif (!$options['negate'] && $shorterThanMinimum) {
			$error = $this->buildError(sprintf('`%s` is shorter than `%s`.', $value, $options['compareTo']));
		}

		$result = $this->buildResult();

		if ($error !== null) {
			$result->pushError($error);
		}

		return $result;
	}

	public function validateOptions(array $options)
	{
		if (array_key_exists('negate', $options)) {
			if (!is_bool($options['negate'])) {
				throw new InvalidArgumentException('Invalid value given for `negate` option in Text\MinimumLengthValidator.');
			}
		}

		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_int($options['compareTo'])) {
				throw new InvalidArgumentException('Invalid value given for `compareTo` option in Text\MinimumLengthValidator');
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
