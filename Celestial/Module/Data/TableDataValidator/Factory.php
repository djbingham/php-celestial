<?php
namespace Celestial\Module\Data\TableDataValidator;

use Celestial\Helper\InternalCacheTrait;
use Celestial\Base\AbstractModuleFactory;
use Celestial\Module\Validation\ValidationModule;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	public function initialise()
	{
		$dependencies = array(
			'validationModule' => $this->getValidationModule(),
			'tableFieldsInsertValidator' => $this->getTableFieldsInsertDataValidator(),
			'tableFieldsUpdateValidator' => $this->getTableFieldsUpdateDataValidator(),
			'tablesInsertValidator' => $this->getTablesInsertDataValidator(),
			'tablesUpdateValidator' => $this->getTablesInsertDataValidator(),
		);

		return new TableDataValidatorModule($dependencies);
	}

	protected function validateOptions()
	{

	}

	/**
	 * @return ValidationModule
	 */
	protected function getValidationModule()
	{
		return $this->app->module('validation');
	}

	protected function getTableFieldsInsertDataValidator()
	{
		return new Validator\TableFieldsInsertValidator($this->app->module('validation'));
	}

	protected function getTableFieldsUpdateDataValidator()
	{
		return new Validator\TableFieldsUpdateValidator($this->app->module('validation'));
	}

	protected function getTablesInsertDataValidator()
	{
		return new Validator\TablesInsertValidator($this->app->module('validation'));
	}

	protected function getTablesUpdateDataValidator()
	{
		return new Validator\TablesUpdateValidator($this->app->module('validation'));
	}
}
