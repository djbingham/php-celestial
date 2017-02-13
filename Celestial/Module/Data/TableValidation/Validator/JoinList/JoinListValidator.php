<?php
namespace Celestial\Module\Data\TableValidation\Validator\JoinList;

use Celestial\Module\Data\TableValidation\Base\BaseValidator;
use Celestial\Module\Data\TableValidation\DependencyManager;
use Celestial\Module\Validation\Face\ValidatorInterface;

class JoinListValidator extends BaseValidator
{
	/**
	 * @var ValidatorInterface
	 */
	private $joinValidator;

	/**
	 * @var ValidatorInterface
	 */
	private $joinAliasValidator;

	/**
	 * @var ValidatorInterface
	 */
	private $listStructureValidator;

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->listStructureValidator = $dependencyManager->getJoinListStructureValidator();
		$this->joinAliasValidator = $dependencyManager->getJoinListAliasValidator();
		$this->joinValidator = $dependencyManager->getJoinValidator();
	}

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($joinList, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		$structureResult = $this->listStructureValidator->validate($joinList);
		if (!$structureResult->isValid()) {
			$error = $this->buildError('Join list structure is invalid', $structureResult->getErrors());
			$errors->push($error);
		}

		foreach ($joinList as $joinAlias => $join) {
			$aliasResult = $this->joinAliasValidator->validate($joinAlias);
			if (!$aliasResult->isValid()) {
				$error = $this->buildError(sprintf('Join alias `%s` is invalid', $joinAlias), $aliasResult->getErrors());
				$errors->push($error);
			}

			$joinResult = $this->joinValidator->validate($join, array('joinAlias' => $joinAlias));

			if (!$joinResult->isValid()) {
				$errorMessage = sprintf('Join with alias `%s` is invalid', $joinAlias);
				$error = $this->buildError($errorMessage, $joinResult->getErrors());
				$errors->push($error);
			}
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
