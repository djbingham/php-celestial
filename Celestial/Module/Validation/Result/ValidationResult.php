<?php
namespace Celestial\Module\Validation\Result;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Validation\Face\ValidationErrorInterface;
use Celestial\Module\Validation\Face\ValidationErrorListInterface;
use Celestial\Module\Validation\Face\ValidationResultInterface;
use Celestial\Module\Validation\Face\ValidatorInterface;

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
		if ($this->errors instanceof ValidationErrorListInterface) {
			$isValid = ($this->errors->length() === 0);
		} else {
			$isValid = true;
		}

		return $isValid;
	}

	public function pushError(ValidationErrorInterface $error)
	{
		$this->errors->push($error);
		return $this;
	}

	public function pushErrors(ValidationErrorListInterface $errors)
	{
		$this->errors->merge($errors);
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
