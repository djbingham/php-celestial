<?php
namespace SlothDemo;

use Sloth\Base\Config as BaseConfig;

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
			'adapter' => array(
				'factoryClass' => 'Sloth\\Module\\Adapter\\Factory',
				'options' => array(
					'adapters' => array(
						'stringBoolean' => array(
							'class' => 'Sloth\\Module\\Adapter\\Adapter\\StringBooleanAdapter',
							'options' => array()
						),
						'stringNull' => array(
							'class' => 'Sloth\\Module\\Adapter\\Adapter\\StringBooleanAdapter',
							'options' => array()
						)
					)
				)
			),
			'authentication' => array(
				'factoryClass' => 'Sloth\\Module\\Authentication\\Factory',
				'options' => array(
					'cookieVerificationResource' => 'authenticationCookie',
					'rememberUser' => true
				)
			),
			'cookie' => array(
				'factoryClass' => 'Sloth\\Module\\Cookie\\Factory',
				'options' => array()
			),
			'dataProvider' => array(
				'factoryClass' => 'Sloth\\Module\\DataProvider\\Factory',
				'options' => array(
					'providers' => array(
						'authentication' => 'Sloth\\Module\\DataProvider\\Provider\\AuthenticationDataProvider',
						'resource' => 'Sloth\\Module\\DataProvider\\Provider\\ResourceProvider',
						'resourceList' => 'Sloth\\Module\\DataProvider\\Provider\\ResourceListProvider',
						'session' => 'Sloth\\Module\\DataProvider\\Provider\\SessionDataProvider',
						'static' => 'Sloth\\Module\\DataProvider\\Provider\\StaticDataProvider'
					)
				)
			),
			'hashing' => array(
				'factoryClass' => 'Sloth\\Module\\Hashing\\Factory',
				'options' => array(
					'salt' => 'SlothDemoHashingSalt'
				)
			),
			'mysql' => array(
				'factoryClass' => 'Sloth\\Module\\MySql\\Factory',
				'options' => array(
					'host' => 'localhost',
					'name' => 'slothDemo',
					'password' => 'Sl0thD3m0P455',
					'username' => 'slothDemo'
				)
			),
			'render' => array(
				'factoryClass' => 'Sloth\\Module\\Render\\Factory',
				'options' => array(
					'viewDirectory' => $this->rootDirectory() . '/Route/View',
					'viewManifestDirectory' => $this->rootDirectory() . '/Route/Manifest'
				)
			),
			'request' => array(
				'factoryClass' => 'Sloth\\Module\\Request\\Factory',
				'options' => array()
			),
			'resource' => array(
				'factoryClass' => 'Sloth\\Module\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'resourceNamespace' => 'SlothDemo\\Resource',
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest'
				)
			),
			'restRender' => array(
				'factoryClass' => 'Sloth\\Module\\Render\\Factory',
				'options' => array(
					'viewDirectory' => $this->rootDirectory() . '/View/Resource',
					'viewManifestDirectory' => null
				)
			),
			'restResource' => array(
				'factoryClass' => 'Sloth\\Module\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'resourceNamespace' => 'SlothDemo\\Resource',
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest'
				)
			),
			'router' => array(
				'factoryClass' => 'Sloth\\Module\\Router\\Factory',
				'options' => array(
					'routes' => new BaseConfig\Routes(array(
						'login' => array(
							'controller' => 'Sloth\\Api\\Authentication\\AuthenticationController'
						),
						'logout' => array(
							'controller' => 'Sloth\\Api\\Authentication\\UnauthenticationController'
						),
						'resource' => array(
							'namespace' => 'Sloth\\Api\\Rest\\Controller'
						)
					)),
					'rootNamespace' => $this->rootNamespace(),
					'defaultController' => 'Sloth\\Api\\View\\ViewController'
				)
			),
			'session' => array(
				'factoryClass' => 'Sloth\\Module\\Session\\Factory',
				'options' => array()
			)
        ));
    }
}
