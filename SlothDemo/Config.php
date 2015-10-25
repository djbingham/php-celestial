<?php
namespace SlothDemo;

use Sloth\Base\Config as BaseConfig;
use Sloth\SlothDefault;

class Config extends BaseConfig
{
	private $rootUrl;

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

    public function modules()
    {
        return new BaseConfig\Modules(array(
			'router' => array(
				'factoryClass' => 'Sloth\\Module\\Router\\Factory',
				'options' => array(
					'routes' => new BaseConfig\Routes(array(
						'resource' => array(
							'namespace' => 'Sloth\\Api\\Rest\\Controller'
						)
					)),
					'rootNamespace' => $this->rootNamespace(),
					'defaultController' => 'SlothDemo\\Controller\\DefaultController'
				)
			),
			'resource' => array(
				'factoryClass' => 'Sloth\\Module\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest',
					'resourceNamespace' => 'SlothDemo\\Resource'
				)
			),
			'render' => array(
				'factoryClass' => 'Sloth\\Module\\Render\\Factory',
				'options' => array(
					'viewManifestDirectory' => $this->rootDirectory() . '/Route/Manifest',
					'viewDirectory' => $this->rootDirectory() . '/Route/View'
				)
			),
			'restResource' => array(
				'factoryClass' => 'Sloth\\Module\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest',
					'resourceNamespace' => 'SlothDemo\\Resource'
				)
			),
			'restRender' => array(
				'factoryClass' => 'Sloth\\Module\\Render\\Factory',
				'options' => array(
					'viewManifestDirectory' => null,
					'viewDirectory' => $this->rootDirectory() . '/View/Resource'
				)
			),
			'mysql' => array(
				'factoryClass' => 'Sloth\\Module\\MySql\\Factory',
				'options' => array(
					'name' => 'slothDemo',
					'host' => 'localhost',
					'username' => 'slothDemo',
					'password' => 'Sl0thD3m0P455'
				)
			)
        ));
    }
}
