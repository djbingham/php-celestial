<?php
namespace Sloth;

use Sloth\Module\ModuleLoader;

abstract class App
{
	/**
	 * @var ModuleLoader
	 */
	protected $moduleLoader;

	/**
	 * @var Base\Config
	 */
	protected $config;

	public function __construct(Base\Config $config)
	{
		$this->config = $config;
	}

	public function setModuleLoader(ModuleLoader $moduleLoader)
	{
		$this->moduleLoader = $moduleLoader;
		return $this;
	}

	public function module($name)
	{
		return $this->moduleLoader->getModule($name);
	}

	public function rootDirectory()
	{
		return $this->config->rootDirectory();
	}

	public function rootNamespace()
	{
		return $this->config->rootNamespace();
	}

	public function rootUrl()
	{
		return $this->config->rootUrl();
	}

	public function moduleLoader()
	{
		return new $this->config->moduleLoader();
	}

	public function createUrl(array $pathParts = array())
	{
		$url = $this->rootUrl();
		if (!empty($pathParts)) {
			$path = implode('/', $pathParts);
			$url = sprintf('%s/%s', $url, $path);
		}
		return rtrim($url, '/');
	}

	public function redirect($newUrl)
	{
		header(sprintf('Location: %s', $newUrl));
		exit;
	}
}
