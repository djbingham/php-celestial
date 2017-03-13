<?php
namespace ToDoList\Config;

use Celestial\Base\Config as BaseConfig;

class AppConfig extends BaseConfig
{
	/**
	 * @var string
	 */
	private $rootUrl;

	/**
	 * @var BaseConfig\Modules
	 */
	private $modulesConfig;

	public function rootUrl()
	{
		if (!isset($this->rootUrl)) {
			$requestProtocol = strpos($_SERVER['SERVER_SIGNATURE'], '443') !== false ? 'https://' : 'http://';
			$this->rootUrl = $requestProtocol . $_SERVER['HTTP_HOST'];
		}
		return $this->rootUrl;
	}

	public function rootDirectory()
	{
		return dirname(__DIR__);
	}

	public function rootNamespace()
	{
		return 'ToDoList';
	}

	public function logModule()
	{
		return 'log';
	}

    public function modules()
    {
    	if ($this->modulesConfig === null) {
			$modulePaths = [
				'adapter' => 'Adapter',
				'authentication' => 'Authentication',
				'cookie' => 'Cookie',
				'data.resource' => 'Data/Resource',
				'data.resourceDataValidator' => 'Data/ResourceDataValidator',
				'data.table' => 'Data/Table',
				'data.tableValidation' => 'Data/TableValidation',
				'data.tableDataValidator' => 'Data/TableDataValidator',
				'data.tableQuery' => 'Data/TableQuery',
				'dataProvider' => 'DataProvider',
				'hashing' => 'Hashing',
				'log' => 'Log',
				'mysql' => 'MySql',
				'render' => 'Render',
				'request' => 'Request',
				'restRender' => 'RestRender',
				'restResource' => 'RestResource',
				'router' => 'Router',
				'session' => 'Session',
				'validation' => 'Validation'
			];

			$moduleConfigs = [];

			foreach ($modulePaths as $moduleKey => $modulePath) {
				$moduleConfigs[$moduleKey] = $this->getModuleConfig($modulePath);
			}

			$this->modulesConfig = new BaseConfig\Modules($moduleConfigs);
		}

		return $this->modulesConfig;
    }

    private function getModuleConfig($modulePath)
	{
		$pathParts = explode('/', sprintf('Config/Module/%s.php', $modulePath));
		$filePath = implode(DIRECTORY_SEPARATOR, $pathParts);

		return require $filePath;
	}
}
