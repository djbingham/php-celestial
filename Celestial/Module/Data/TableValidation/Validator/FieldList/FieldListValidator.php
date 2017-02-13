<?php
namespace Celestial\Module\Data\TableValidation\Validator\FieldList;

use Celestial\Module\Data\TableValidation\Base\BaseValidator;
use Celestial\Module\Data\TableValidation\DependencyManager;
use Celestial\Module\Validation\Face\ValidatorInterface;

class FieldListValidator extends BaseValidator
{
	/**
	 * @var ValidatorInterface
	 */
	private $fieldValidator;

	/**
	 * @var ValidatorInterface
	 */
	private $fieldAliasValidator;

	/**
	 * @var ValidatorInterface
	 */
	private $listStructureValidator;

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->listStructureValidator = $dependencyManager->getFieldListStructureValidator();
		$this->fieldAliasValidator = $dependencyManager->getFieldListAliasValidator();
		$this->fieldValidator = $dependencyManager->getFieldValidator();
	}

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($validatorList, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		$structureResult = $this->listStructureValidator->validate($validatorList);
		if (!$structureResult->isValid()) {
			$error = $this->buildError('Field list structure is invalid', $structureResult->getErrors());
			$errors->push($error);
		}

		foreach ($validatorList as $fieldAlias => $field) {
			$aliasResult = $this->fieldAliasValidator->validate($fieldAlias);
			if (!$aliasResult->isValid()) {
				$error = $this->buildError(sprintf('Field alias `%s` is invalid', $fieldAlias), $aliasResult->getErrors());
				$errors->push($error);
			}

			$fieldResult = $this->fieldValidator->validate($field);
			if (!$fieldResult->isValid()) {
				$errorMessage = sprintf('Field with alias `%s` is invalid', $fieldAlias);
				$error = $this->buildError($errorMessage, $fieldResult->getErrors());
				$errors->push($error);
			}
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
