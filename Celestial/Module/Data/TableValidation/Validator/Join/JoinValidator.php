<?php
namespace Celestial\Module\Data\TableValidation\Validator\Join;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Data\TableValidation\Base\BaseValidator;
use Celestial\Module\Data\TableValidation\DependencyManager;
use Celestial\Module\Validation\Face\ValidatorInterface;

class JoinValidator extends BaseValidator
{
	/**
	 * @var ValidatorInterface
	 */
	private $structureValidator;

	/**
	 * @var array
	 */
	private $propertyValidators = array();

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->structureValidator = $dependencyManager->getJoinStructureValidator();
		$this->propertyValidators = array(
			'type' => $dependencyManager->getJoinTypeValidator(),
			'table' => $dependencyManager->getJoinTableValidator(),
			'onInsert' => $dependencyManager->getJoinOnInsertValidator(),
			'onUpdate' => $dependencyManager->getJoinOnUpdateValidator(),
			'onDelete' => $dependencyManager->getJoinOnDeleteValidator(),
			'via' => $dependencyManager->getJoinViaValidator(),
			'joins' => $dependencyManager->getJoinJoinsValidator()
		);
	}

	public function validateOptions(array $options)
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (!array_key_exists('joinAlias', $options)) {
			$error = $this->buildError('Missing `joinAlias` option for join validator');
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}

	public function validate($join, array $options = array())
	{
		$optionsResult = $this->validateOptions($options);

		if (!$optionsResult->isValid()) {
			throw new InvalidArgumentException('Invalid options given to join validator');
		}

		$errors = $this->validationModule->buildValidationErrorList();

		$structureResult = $this->structureValidator->validate($join);
		if (!$structureResult->isValid()) {
			$error = $this->buildError(sprintf('Join structure is invalid'), $structureResult->getErrors());
			$errors->push($error);
		}

		foreach ($this->propertyValidators as $propertyName => $propertyValidator) {
			if (property_exists($join, $propertyName)) {
				$propertyResult = $this->executeValidator($propertyValidator, $join, $options);

				if (!$propertyResult->isValid()) {
					$errorMessage = sprintf('Value of `%s` property is invalid', $propertyName);
					$error = $this->buildError($errorMessage, $propertyResult->getErrors());
					$errors->push($error);
				}
			}
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}

	private function executeValidator(ValidatorInterface $validator, $value, array $options)
	{
		return $validator->validate($value, $options);
	}
}
