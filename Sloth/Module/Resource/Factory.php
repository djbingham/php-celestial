<?php
namespace Sloth\Module\Resource;

use Helper\InternalCacheTrait;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	public function initialise()
	{
		$module = new ModuleCore($this->app);
		$module->setTableManifestDirectory($this->options['tableManifestDirectory'])
			->setResourceManifestDirectory($this->options['resourceManifestDirectory'])
			->setResourceNamespace($this->options['resourceNamespace'])
			->setTableManifestValidator($this->getTableManifestValidator())
			->setResourceManifestValidator($this->getResourceManifestValidator());
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
}
