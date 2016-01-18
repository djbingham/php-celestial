<?php
namespace Sloth\Module\Data\TableValidation\Validator\ValidatorList;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Data\TableValidation\Validator\Validator\ValidatorValidator;

class ValidatorListValidator extends BaseValidator
{
	/**
	 * @var StructureValidator
	 */
	private $listStructureValidator;

	/**
	 * @var ValidatorValidator
	 */
	private $validatorValidator;

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->listStructureValidator = $dependencyManager->getValidatorListStructureValidator();
		$this->validatorValidator = $dependencyManager->getValidatorValidator();
	}

	public function validateOptions(array $options)
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (!array_key_exists('tableManifest', $options)) {
			$error = $this->buildError(
				'Missing `tableManifest` in options given to validator for table validators list'
			);
			$errors->push($error);
		} elseif (!is_object($options['tableManifest'])) {
			$error = $this->buildError(
				'Invalid `tableManifest` option given to validator for table validators list (must be an object)'
			);
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}

	public function validate($validatorList, array $options = array())
	{
		$optionsResult = $this->validateOptions($options);

		if (!$optionsResult->isValid()) {
			throw new InvalidArgumentException('Invalid options given to validator for validator property `options`');
		}

		$tableManifest = $options['tableManifest'];

		$errors = $this->validationModule->buildValidationErrorList();

		$structureResult = $this->listStructureValidator->validate($validatorList);
		if (!$structureResult->isValid()) {
			$error = $this->buildError('Validator list structure is invalid', $structureResult->getErrors());
			$errors->push($error);
		}

		foreach ($validatorList as $index => $validator) {
			$validatorResult = $this->validatorValidator->validate($validator, array('tableManifest' => $tableManifest));

			if (!$validatorResult->isValid()) {
				$errorMessage = sprintf('Validator at index `%s` is invalid', $index);
				$error = $this->buildError($errorMessage, $validatorResult->getErrors());
				$errors->push($error);
			}
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
