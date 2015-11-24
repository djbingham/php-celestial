<?php
namespace Sloth\Module\Data\ResourceDataValidator;

use Helper\InternalCacheTrait;
use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	public function initialise()
	{
		$dependencies = array(
			'tableDataValidator' => $this->getTableDataValidator(),
			'resourceAttributesValidator' => $this->getResourceAttributesDataValidator(),
			'resourceValidator' => $this->getResourceDataValidator()
		);

		return new ResourceDataValidatorModule($dependencies);
	}

	protected function validateOptions()
	{

	}

	protected function getTableDataValidator()
	{
		return $this->app->module('data.tableDataValidator');
	}

	protected function getResourceAttributesDataValidator()
	{
		return new Validator\ResourceAttributesValidator($this->app->module('validation'));
	}

	protected function getResourceDataValidator()
	{
		return new Validator\ResourceValidator($this->app->module('validation'));
	}
}
