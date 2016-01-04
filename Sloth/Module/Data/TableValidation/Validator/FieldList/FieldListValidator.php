<?php
namespace Sloth\Module\Data\TableValidation\Validator\FieldList;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Validation\Face\ValidatorInterface;

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

	public function validate($fieldList, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		$structureResult = $this->listStructureValidator->validate($fieldList);
		if (!$structureResult->isValid()) {
			$error = $this->buildError('Field list structure is invalid', $structureResult->getErrors());
			$errors->push($error);
		}

		foreach ($fieldList as $fieldAlias => $field) {
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
