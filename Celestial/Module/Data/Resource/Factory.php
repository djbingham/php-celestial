<?php
namespace Celestial\Module\Data\Resource;

use Celestial\Helper\InternalCacheTrait;
use Celestial\Exception\InvalidArgumentException;
use Celestial\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	public function initialise()
	{
		$module = new ResourceModule($this->app);

		$module->setResourceManifestDirectory($this->options['resourceManifestDirectory'])
			->setResourceManifestValidator($this->getResourceManifestValidator())
			->setResourceNamespace($this->options['resourceNamespace'])
			->setTableModule($this->getTableModule())
			->setTableQueryModule($this->getTableQueryModule())
			->setDataValidator($this->getResourceDataValidator());

		return $module;
	}

	protected function validateOptions()
	{
		$required = array(
			'resourceManifestDirectory',
			'resourceNamespace'
		);

		$missing = array_diff($required, array_keys($this->options));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required options for Resource module: ' . implode(', ', $missing)
			);
		}

		if (!is_dir($this->options['resourceManifestDirectory'])) {
			throw new InvalidArgumentException('Invalid resource directory given in options for Resource module');
		}
	}

	protected function getDatabaseWrapper()
	{
		return $this->app->module('mysql');
	}

	protected function getTableModule()
	{
		return $this->app->module('data.table');
	}

	protected function getTableQueryModule()
	{
		return $this->app->module('data.tableQuery');
	}

	protected function getResourceDataValidator()
	{
		return $this->app->module('data.resourceDataValidator');
	}

	protected function getResourceManifestValidator()
	{
		if (!$this->isCached('resourceManifestValidator')) {
			$this->setCached('resourceManifestValidator', new ResourceManifestValidator());
		}
		return $this->getCached('resourceManifestValidator');
	}
}
