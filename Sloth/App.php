<?php
namespace Sloth;

use Sloth\Module\Log\LogModule;
use Sloth\Module\ModuleLoader;

class App
{
	/**
	 * @var ModuleLoader
	 */
	protected $moduleLoader;

	/**
	 * @var Base\Config
	 */
	protected $config;

	/**
	 * @var LogModule
	 */
	protected $logger;

	public function __construct(Base\Config $config)
	{
		$this->config = $config;
	}

	public function setModuleLoader(ModuleLoader $moduleLoader)
	{
		$this->moduleLoader = $moduleLoader;
		return $this;
	}

	/**
	 * @return LogModule
	 */
	public function getLogModule()
	{
		if ($this->logger === null) {
			$this->logger = $this->module($this->config->logModule());
		}
		return $this->logger;
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
		$this->getLogModule()->logInfo(sprintf('Redirecting to `%s`', $newUrl));
		header(sprintf('Location: %s', $newUrl));
		exit;
	}
}
