<?php
namespace Sloth\Module\Data\TableValidation\Validator\Join\Property;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Data\Table\TableModule;

class TableValidator extends BaseValidator
{
	/**
	 * @var TableModule
	 */
	private $tableModule;

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->tableModule = $dependencyManager->getTableModule();
	}

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($join, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		$value = null;
		if (is_object($join) && property_exists($join, 'table')) {
			$value = $join->table;
		}

		if (is_string($value)) {
			if (!$this->tableModule->exists($value)) {
				$error = $this->buildError(
					sprintf('No manifest found for a table named `%s`', $value)
				);
				$errors->push($error);
			}
		} else {
			$error = $this->buildError('Join table value must be a string');
			$errors->push($error);
		}


		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
