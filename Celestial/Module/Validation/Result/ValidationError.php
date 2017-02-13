<?php
namespace Celestial\Module\Validation\Result;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Validation\Face\ValidationErrorInterface;
use Celestial\Module\Validation\Face\ValidationErrorListInterface;
use Celestial\Module\Validation\Face\ValidatorInterface;

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

	/**
	 * @var ValidationErrorListInterface
	 */
	private $children;

	public function __construct(array $properties)
	{
		$this->validateProperties($properties);

		if (array_key_exists('message', $properties)) {
			$this->message = $properties['message'];
		}

		if (array_key_exists('validator', $properties)) {
			$this->validator = $properties['validator'];
		}

		if (array_key_exists('children', $properties)) {
			$this->children = $properties['children'];
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

	public function getChildren()
	{
		return $this->children;
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

		if (array_key_exists('children', $properties) && !($properties['children'] instanceof ValidationErrorListInterface)) {
			throw new InvalidArgumentException(
				'Children property given to ValidationResult does not implement ValidationErrorListInterface'
			);
		}

		if (!is_string($properties['message'])) {
			throw new InvalidArgumentException('Message given to ValidationResult is not a string');
		}
	}
}
