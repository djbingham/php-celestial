<?php
namespace Sloth\Demo;

use Sloth\Base\Config as BaseConfig;
use SlothDefault;

class Config extends BaseConfig
{
	private $rootUrl;
	private $database;
	private $defaultController;

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
		return __DIR__;
	}

	public function rootNamespace()
	{
		return 'Sloth\Demo';
	}

	public function defaultController()
	{
		if (!isset($this->defaultController)) {
			$this->defaultController = 'Sloth\\Demo\\Controller\\DefaultController';
		}
		return $this->defaultController;
	}

	public function database()
	{
		if (!isset($this->database)) {
			return new BaseConfig\Database(array(
				'name' => 'slothDemo',
				'host' => 'localhost',
				'username' => 'slothDemo',
				'password' => 'Sl0thD3m0P455'
			));
		}
		return $this->database;
	}

	public function routes()
	{
		return new BaseConfig\Routes(array(
			'resource' => array(
				'namespace' => 'Sloth\\Api\\Rest\\Controller'
			)
		));
	}

    public function modules()
    {
        return new BaseConfig\Modules(array(
			'resource' => array(
				'factoryClass' => 'Sloth\\Demo\\Module\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest',
					'resourceNamespace' => 'Sloth\\Demo\\Resource'
				)
			),
			'render' => array(
				'factoryClass' => 'Sloth\\Demo\\Module\\Render\\Factory',
				'options' => array(
					'viewManifestDirectory' => $this->rootDirectory() . '/Route/Manifest',
					'viewDirectory' => $this->rootDirectory() . '/Route/View'
				)
			),
			'resourceRender' => array(
				'factoryClass' => 'Sloth\\Demo\\Module\\Render\\Factory',
				'options' => array(
					'viewManifestDirectory' => null,
					'viewDirectory' => $this->rootDirectory() . '/View/Resource'
				)
			)
        ));
    }

	public function initialisation()
	{
		return new SlothDefault\Initialisation($this);
	}
}
