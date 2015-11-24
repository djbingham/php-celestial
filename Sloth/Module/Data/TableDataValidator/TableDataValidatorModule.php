<?php
namespace Sloth\Module\Data\TableDataValidator;

use Sloth\Module\DataTable\Face\TableInterface;
use Sloth\Module\DataTableQuery\Face\TableValidatorInterface;

class TableDataValidatorModule
{
	/**
	 * @var TableValidatorInterface
	 */
	private $tableFieldsInsertValidator;

	/**
	 * @var TableValidatorInterface
	 */
	private $tableFieldsUpdateValidator;

	/**
	 * @var TableValidatorInterface
	 */
	private $tablesInsertValidator;

	/**
	 * @var TableValidatorInterface
	 */
	private $tablesUpdateValidator;

	public function __construct(array $subValidators)
	{
		$this->tableFieldsInsertValidator = $subValidators['tableFieldsInsertValidator'];
		$this->tableFieldsUpdateValidator = $subValidators['tableFieldsUpdateValidator'];
		$this->tablesInsertValidator = $subValidators['tablesInsertValidator'];
		$this->tablesUpdateValidator = $subValidators['tablesUpdateValidator'];
	}

	public function validateInsertData(TableInterface $tableDefinition, array $attributes)
	{
		return $this->tableFieldsInsertValidator->validate($tableDefinition, $attributes)
		&& $this->tablesInsertValidator->validate($tableDefinition, $attributes);
	}

	public function validateUpdateData(TableInterface $tableDefinition, array $attributes)
	{
		return $this->tableFieldsUpdateValidator->validate($tableDefinition, $attributes)
		&& $this->tablesUpdateValidator->validate($tableDefinition, $attributes);
	}
}
