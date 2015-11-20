<?php
namespace Sloth\Module\Resource;

use Helper\InternalCacheTrait;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	public function initialise()
	{
		$module = new ResourceModule($this->app);
		$module
			->setDatabaseWrapper($this->getDatabaseWrapper())
			->setTableManifestValidator($this->getTableManifestValidator())
			->setResourceManifestValidator($this->getResourceManifestValidator())
			->setDataValidator($this->getDataValidator())
			->setTableManifestDirectory($this->options['tableManifestDirectory'])
			->setResourceManifestDirectory($this->options['resourceManifestDirectory'])
			->setResourceNamespace($this->options['resourceNamespace']);
		return $module;
	}

	protected function validateOptions()
	{
		$required = array(
			'tableManifestDirectory',
			'resourceManifestDirectory',
			'resourceNamespace'
		);

		$missing = array_diff($required, array_keys($this->options));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required options for Resource module: ' . implode(', ', $missing)
			);
		}

		if (!is_dir($this->options['tableManifestDirectory'])) {
			throw new InvalidArgumentException('Invalid table directory given in options for Render module');
		}
		if (!is_dir($this->options['resourceManifestDirectory'])) {
			throw new InvalidArgumentException('Invalid resource directory given in options for Render module');
		}
	}

	protected function getDatabaseWrapper()
	{
		return $this->app->module('mysql');
	}

	protected function getTableManifestValidator()
	{
		if (!$this->isCached('tableManifestValidator')) {
			$this->setCached('tableManifestValidator', new TableManifestValidator());
		}
		return $this->getCached('tableManifestValidator');
	}

	protected function getResourceManifestValidator()
	{
		if (!$this->isCached('resourceManifestValidator')) {
			$this->setCached('resourceManifestValidator', new ResourceManifestValidator());
		}
		return $this->getCached('resourceManifestValidator');
	}

	protected function getDataValidator()
	{
		if (!$this->isCached('dataValidator')) {
			$this->setCached('dataValidator', new DataValidator(array(
				'tableFieldsInsertValidator' => new Validator\TableFieldsInsertValidator($this->app->module('validation')),
				'tableFieldsUpdateValidator' => new Validator\TableFieldsUpdateValidator($this->app->module('validation')),
				'resourceAttributesValidator' => new Validator\ResourceAttributesValidator($this->app->module('validation')),
				'tablesInsertValidator' => new Validator\TablesInsertValidator($this->app->module('validation')),
				'tablesUpdateValidator' => new Validator\TablesUpdateValidator($this->app->module('validation')),
				'resourceValidator' => new Validator\ResourceValidator($this->app->module('validation'))
			)));
		}
		return $this->getCached('dataValidator');
	}
}
