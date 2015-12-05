<?php
namespace Sloth\Module\Validation\Result;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidationErrorInterface;
use Sloth\Module\Validation\Face\ValidationErrorListInterface;
use Sloth\Module\Validation\Face\ValidationResultInterface;
use Sloth\Module\Validation\Face\ValidatorInterface;

class ValidationResult implements ValidationResultInterface
{
	/**
	 * @var ValidationErrorListInterface
	 */
	private $errors;

	/**
	 * @var ValidatorInterface
	 */
	protected $validator;

	public function __construct(array $properties)
	{
		$this->validateProperties($properties);

		if (array_key_exists('errors', $properties)) {
			$this->errors = $properties['errors'];
		}

		if (array_key_exists('validator', $properties)) {
			$this->validator = $properties['validator'];
		}
	}

	public function isValid()
	{
		return ($this->errors->length() === 0);
	}

	public function pushError(ValidationErrorInterface $error)
	{
		$this->errors->push($error);
		return $this;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function getValidator()
	{
		return $this->validator;
	}

	protected function validateProperties(array $properties)
	{
		if (!array_key_exists('errors', $properties)) {
			throw new InvalidArgumentException('Missing `errors` property for ValidationResult instance');
		}

		if (array_key_exists('validator', $properties) && !($properties['validator'] instanceof ValidatorInterface)) {
			throw new InvalidArgumentException(
				'Validator given to ValidationResult does not implement ValidatorInterface'
			);
		}
	}
}
