<?php
namespace Sloth\Module\Data\TableValidation\Validator\Validator;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Validation\Face\ValidatorInterface;

class ValidatorValidator extends BaseValidator
{
	/**
	 * @var ValidatorInterface
	 */
	private $structureValidator;

	/**
	 * @var ValidatorInterface
	 */
	private $fieldsValidator;

	/**
	 * @var ValidatorInterface
	 */
	private $optionsValidator;

	/**
	 * @var ValidatorInterface
	 */
	private $ruleValidator;

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->structureValidator = $dependencyManager->getValidatorStructureValidator();
		$this->fieldsValidator = $dependencyManager->getValidatorFieldsValidator();
		$this->optionsValidator = $dependencyManager->getValidatorOptionsValidator();
		$this->ruleValidator = $dependencyManager->getValidatorRuleValidator();
	}

	public function validateOptions(array $options)
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (!array_key_exists('tableManifest', $options)) {
			$error = $this->buildError(
				'Missing `tableManifest` in options given to validator for table validator'
			);
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}

	public function validate($validator, array $options = array())
	{
		$optionsResult = $this->validateOptions($options);

		if (!$optionsResult->isValid()) {
			throw new InvalidArgumentException('Invalid options given to validator for validator property `options`');
		}

		$tableManifest = $options['tableManifest'];
		$tableFields = $tableManifest->fields;

		$errors = $this->validationModule->buildValidationErrorList();

		$structureResult = $this->structureValidator->validate($validator);
		if (!$structureResult->isValid()) {
			$error = $this->buildError(sprintf('Validator structure is invalid'), $structureResult->getErrors());
			$errors->push($error);
		}

		$fieldsResult = $this->fieldsValidator->validate(
			$validator->fields,
			array('tableFields' => $tableFields)
		);
		if (!$fieldsResult->isValid()) {
			$errorMessage = 'Value of `fields` property is invalid';
			$error = $this->buildError($errorMessage, $fieldsResult->getErrors());
			$errors->push($error);
		}

		if (property_exists($validator, 'options')) {
			$optionsResult = $this->optionsValidator->validate(
				(array)$validator->options,
				array('rule' => $validator->rule)
			);

			if (!$optionsResult->isValid()) {
				$errorMessage = 'Value of `options` property is invalid';
				$error = $this->buildError($errorMessage, $optionsResult->getErrors());
				$errors->push($error);
			}
		}

		$ruleResult = $this->ruleValidator->validate($validator->rule, array());
		if (!$ruleResult->isValid()) {
			$errorMessage = 'Value of `rule` property is invalid';
			$error = $this->buildError($errorMessage, $ruleResult->getErrors());
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
