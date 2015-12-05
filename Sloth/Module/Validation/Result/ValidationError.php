<?php
namespace Sloth\Module\Validation\Result;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidationErrorInterface;
use Sloth\Module\Validation\Face\ValidatorInterface;

class ValidationError implements ValidationErrorInterface
{
	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var ValidatorInterface
	 */
	private $validator;

	public function __construct(array $properties)
	{
		$this->validateProperties($properties);

		if (array_key_exists('message', $properties)) {
			$this->message = $properties['message'];
		}

		if (array_key_exists('validator', $properties)) {
			$this->validator = $properties['validator'];
		}
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function getValidator()
	{
		return $this->validator;
	}

	protected function validateProperties(array $properties)
	{
		$required = array('message');

		$missing = array_diff($required, array_keys($properties));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required properties for validation error: ' . implode(', ', $missing)
			);
		}

		if (array_key_exists('validator', $properties) && !($properties['validator'] instanceof ValidatorInterface)) {
			throw new InvalidArgumentException(
				'Validator given to ValidationResult does not implement ValidatorInterface'
			);
		}

		if (!is_string($properties['message'])) {
			throw new InvalidArgumentException('Message given to ValidationResult is not a string');
		}
	}
}
