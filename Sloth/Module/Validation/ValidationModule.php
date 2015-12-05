<?php
namespace Sloth\Module\Validation;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidationResultFactoryInterface;
use Sloth\Module\Validation\Face\ValidatorInterface;

class ValidationModule
{
	/**
	 * @var array
	 */
	private $validators = array();

	/**
	 * @var ValidationResultFactoryInterface
	 */
	private $resultFactory;

	/**
	 * @param ValidationResultFactoryInterface $resultFactory
	 * @return $this
	 */
	public function setResultFactory(ValidationResultFactoryInterface $resultFactory)
	{
		$this->resultFactory = $resultFactory;
		return $this;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function validatorExists($name)
	{
		return array_key_exists($name, $this->validators);
	}

	/**
	 * @param string $name
	 * @param ValidatorInterface $validator
	 * @return $this
	 */
	public function setValidator($name, ValidatorInterface $validator)
	{
		$this->validators[$name] = $validator;
		return $this;
	}

	/**
	 * @param string $name
	 * @return ValidatorInterface
	 * @throws InvalidArgumentException
	 */
	public function getValidator($name)
	{
		if (!$this->validatorExists($name)) {
			throw new InvalidArgumentException(
				sprintf('Validator not found with name `%s`', $name)
			);
		}
		return $this->validators[$name];
	}

	public function buildValidationResultList(array $results = array())
	{
		return $this->resultFactory->buildResultList($results);
	}

	public function buildValidationResult(array $properties = array())
	{
		return $this->resultFactory->buildResult($properties);
	}

	public function buildValidationErrorList(array $errors = array())
	{
		return $this->resultFactory->buildErrorList($errors);
	}

	public function buildValidationError(array $properties = array())
	{
		return $this->resultFactory->buildError($properties);
	}
}
