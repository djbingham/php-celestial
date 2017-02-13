<?php
namespace Celestial;

use Celestial\Module\Log\Face\LoggerInterface;
use Celestial\Module\Log\LogModule;
use Celestial\Module\ModuleLoader;

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
	protected $logModule;

	/**
	 * @var Module\Log\Face\LoggerInterface
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
	 * @return LoggerInterface
	 */
	public function getLogModule()
	{
		if ($this->logger === null) {
			$this->logModule = $this->module($this->config->logModule());

			$this->logger = $this->logModule->createLogger($this);
		}
		return $this->logModule;
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
		$this->logger->info(sprintf('Redirecting to `%s`', $newUrl));
		header(sprintf('Location: %s', $newUrl));
		exit;
	}
}
