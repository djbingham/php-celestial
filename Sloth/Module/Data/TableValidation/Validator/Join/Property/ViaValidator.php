<?php
namespace Sloth\Module\Data\TableValidation\Validator\Join\Property;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\DependencyManager;

class ViaValidator extends BaseValidator
{
	/**
	 * @var Via\TableAliasValidator
	 */
	private $tableAliasValidator;

	/**
	 * @var Via\TableNameValidator
	 */
	private $tableNameValidator;

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->tableAliasValidator = $dependencyManager->getJoinViaTableAliasValidator();
		$this->tableNameValidator = $dependencyManager->getJoinViaTableNameValidator();
	}

	public function validateOptions(array $options)
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (!array_key_exists('joinAlias', $options)) {
			$error = $this->buildError('Missing `joinAlias` in options given to validator for join property `via`');
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
			throw new InvalidArgumentException('Invalid options given to validator for join property `via`');
		}

		$errors = $this->validationModule->buildValidationErrorList();

		foreach ($join->via as $tableAlias => $tableName) {
			$tableAliasResult = $this->tableAliasValidator->validate($tableAlias);
			if (!$tableAliasResult->isValid()) {
				$error = $this->buildError(
					sprintf('Table alias `%s` in join property `via` is invalid', $tableAlias),
					$tableAliasResult->getErrors()
				);
				$errors->push($error);
			}

			$tableNameResult = $this->tableNameValidator->validate($tableName);
			if (!$tableNameResult->isValid()) {
				$error = $this->buildError(
					sprintf('Table name `%s` in join property `via` is invalid', $tableName),
					$tableNameResult->getErrors()
				);
				$errors->push($error);
			}
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
