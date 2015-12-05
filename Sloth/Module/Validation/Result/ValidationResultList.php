<?php
namespace Sloth\Module\Validation\Result;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Helper\ObjectListTrait;
use Sloth\Module\Validation\Face\ValidationErrorInterface;
use Sloth\Module\Validation\Face\ValidationResultInterface;
use Sloth\Module\Validation\Face\ValidationResultListInterface;
use Sloth\Module\Validation\Face\ValidatorInterface;

class ValidationResultList implements ValidationResultListInterface
{
	use ObjectListTrait;

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * @var ValidatorInterface
	 */
	protected $validator;

	public function __construct(array $properties = array())
	{
		$properties = $this->validateProperties($properties);

		if (array_key_exists('errors', $properties)) {
			$this->errors = $properties['errors'];
		}

		if (array_key_exists('validator', $properties)) {
			$this->validator = $properties['validator'];
		}
	}

	public function isValid()
	{
		$isValid = true;

		/** @var ValidationResultInterface $result */
		foreach ($this->items as $result) {
			if ($result->isValid()) {
				$isValid = false;
			}
		}

		return $isValid;
	}

	public function pushResult(ValidationResultInterface $result)
	{
		$this->append($result);
		return $this;
	}

	public function pushError(ValidationErrorInterface $error)
	{
		$this->errors[] = $error;
		return $this;
	}

	public function getErrors()
	{
		$errors = new ValidationErrorList();

		/** @var ValidationResultInterface $result */
		foreach ($this->items as $result) {
			foreach ($result->getErrors() as $error) {
				$errors->push($error);
			}
		}

		foreach ($this->errors as $error) {
			$errors->push($error);
		}

		return $errors;
	}

	public function getValidator()
	{
		return $this->validator;
	}

	protected function validateProperties(array $properties)
	{
		if (array_key_exists('validator', $properties) && !($properties['validator'] instanceof ValidatorInterface)) {
			throw new InvalidArgumentException(
				'Validator given to ValidationResult does not implement ValidatorInterface'
			);
		}

		return $properties;
	}
}
