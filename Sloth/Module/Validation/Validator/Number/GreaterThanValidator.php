<?php
namespace Sloth\Module\Validation\Validator\Number;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Base\AbstractValidator;

class GreaterThanValidator extends AbstractValidator
{
	public function validate($value, array $options = array())
	{
		$this->validateOptions($options);
		$options = $this->padOptions($options);

		$valueIsGreater = $value > $options['compareTo'];
		$error = null;

		if ($options['negate'] && $valueIsGreater) {
			$error = $this->buildError(sprintf('`%s` is greater than `%s`.', $value, $options['compareTo']));
		} elseif (!$options['negate'] && !$valueIsGreater) {
			$error = $this->buildError(sprintf('`%s` is not greater than `%s`.', $value, $options['compareTo']));
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
				throw new InvalidArgumentException('Invalid value given for `negate` option in Number\GreaterThanValidator.');
			}
		}

		if (array_key_exists('compareTo', $options)) {
			if (!is_null($options['compareTo']) && !is_int($options['compareTo'])) {
				throw new InvalidArgumentException(
					'Invalid value given for `compareTo` option in Number\GreaterThanValidator'
				);
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
