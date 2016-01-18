<?php
namespace Sloth\Module\Data\TableValidation\Validator\TableManifest;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Data\TableValidation\Validator\ValidatorList\ValidatorListValidator;
use Sloth\Module\Data\TableValidation\Validator\FieldList;
use Sloth\Module\Data\TableValidation\Validator\Join;

class TableManifestValidator extends BaseValidator
{
	/**
	 * @var StructureValidator
	 */
	private $structureValidator;

	/**
	 * @var FieldList\FieldListValidator
	 */
	private $fieldListValidator;

	/**
	 * @var \Sloth\Module\Data\TableValidation\Validator\JoinList\JoinListValidator
	 */
	private $joinListValidator;

	/**
	 * @var ValidatorListValidator
	 */
	private $validatorListValidator;

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->structureValidator = $dependencyManager->getTableManifestStructureValidator();
		$this->fieldListValidator = $dependencyManager->getFieldListValidator();
		$this->joinListValidator = $dependencyManager->getJoinListValidator();
		$this->validatorListValidator = $dependencyManager->getValidatorListValidator();
	}

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($tableManifest, array $options = array())
	{
		$resultList = $this->validationModule->buildValidationResultList();

		$resultList->pushResult($this->structureValidator->validate($tableManifest));
		$resultList->pushResult($this->fieldListValidator->validate($tableManifest->fields));

		if (property_exists($tableManifest, 'links')) {
			$resultList->pushResult($this->joinListValidator->validate($tableManifest->links));
		}

		if (property_exists($tableManifest, 'validators')) {
			$resultList->pushResult($this->validatorListValidator->validate(
				$tableManifest->validators,
				array('tableManifest' => $tableManifest)
			));
		}

		return $this->validationModule->flattenResultList($resultList);
	}
}
