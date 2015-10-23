<?php
namespace SlothDemo;

use Sloth\Base\Config as BaseConfig;
use Sloth\SlothDefault;

class Config extends BaseConfig
{
	private $rootUrl;
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
		return 'SlothDemo';
	}

	public function defaultController()
	{
		if (!isset($this->defaultController)) {
			$this->defaultController = 'SlothDemo\\Controller\\DefaultController';
		}
		return $this->defaultController;
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
				'factoryClass' => 'SlothDemo\\Module\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest',
					'resourceNamespace' => 'SlothDemo\\Resource'
				)
			),
			'render' => array(
				'factoryClass' => 'SlothDemo\\Module\\Render\\Factory',
				'options' => array(
					'viewManifestDirectory' => $this->rootDirectory() . '/Route/Manifest',
					'viewDirectory' => $this->rootDirectory() . '/Route/View'
				)
			),
			'restResource' => array(
				'factoryClass' => 'SlothDemo\\Module\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest',
					'resourceNamespace' => 'SlothDemo\\Resource'
				)
			),
			'restRender' => array(
				'factoryClass' => 'SlothDemo\\Module\\Render\\Factory',
				'options' => array(
					'viewManifestDirectory' => null,
					'viewDirectory' => $this->rootDirectory() . '/View/Resource'
				)
			),
			'mysql' => array(
				'factoryClass' => 'SlothDemo\\Module\\MySql\\Factory',
				'options' => array(
					'name' => 'slothDemo',
					'host' => 'localhost',
					'username' => 'slothDemo',
					'password' => 'Sl0thD3m0P455'
				)
			)
        ));
    }

	public function initialisation()
	{
		return new SlothDefault\Initialisation($this);
	}
}
