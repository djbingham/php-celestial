<?php
namespace CelestialDemo;

use Celestial\Base\Config as BaseConfig;

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
		return 'CelestialDemo';
	}

	public function logModule()
	{
		return 'log';
	}

    public function modules()
    {
        return new BaseConfig\Modules(array(
			'adapter' => array(
				'factoryClass' => 'Celestial\\Module\\Adapter\\Factory',
				'options' => array(
					'adapters' => array(
						'stringBoolean' => array(
							'class' => 'Celestial\\Module\\Adapter\\Adapter\\StringBooleanAdapter',
							'options' => array()
						),
						'stringNull' => array(
							'class' => 'Celestial\\Module\\Adapter\\Adapter\\StringBooleanAdapter',
							'options' => array()
						)
					)
				)
			),
			'authentication' => array(
				'factoryClass' => 'Celestial\\Module\\Authentication\\Factory',
				'options' => array(
					'cookieVerificationResource' => 'authenticationCookie',
					'rememberUser' => true
				)
			),
			'cookie' => array(
				'factoryClass' => 'Celestial\\Module\\Cookie\\Factory',
				'options' => array()
			),
			'data.resource' => array(
				'factoryClass' => 'Celestial\\Module\\Data\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'resourceNamespace' => $this->rootNamespace() . '\\Resource'
				)
			),
			'data.resourceDataValidator' => array(
				'factoryClass' => 'Celestial\\Module\\Data\\ResourceDataValidator\\Factory',
				'options' => array()
			),
			'data.table' => array(
				'factoryClass' => 'Celestial\\Module\\Data\\Table\\Factory',
				'options' => array(
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest'
				)
			),
			'data.tableValidation' => array(
				'factoryClass' => 'Celestial\\Module\\Data\\TableValidation\\Factory',
				'options' => array()
			),
			'data.tableDataValidator' => array(
				'factoryClass' => 'Celestial\\Module\\Data\\TableDataValidator\\Factory',
				'options' => array()
			),
			'data.tableQuery' => array(
				'factoryClass' => 'Celestial\\Module\\Data\\TableQuery\\Factory',
				'options' => array()
			),
			'dataProvider' => array(
				'factoryClass' => 'Celestial\\Module\\DataProvider\\Factory',
				'options' => array(
					'providers' => array(
						'authentication' => 'Celestial\\Module\\DataProvider\\Provider\\AuthenticationDataProvider',
						'jsonFile' => 'Celestial\\Module\\DataProvider\\Provider\\JsonFileProvider',
						'request.getProperty' => 'Celestial\\Module\\DataProvider\\Provider\\Request\\GetParameterDataProvider',
						'request' => 'Celestial\\Module\\DataProvider\\Provider\\RequestProvider',
						'resource' => 'Celestial\\Module\\DataProvider\\Provider\\ResourceProvider',
						'resourceList' => 'Celestial\\Module\\DataProvider\\Provider\\ResourceListProvider',
						'session' => 'Celestial\\Module\\DataProvider\\Provider\\SessionDataProvider',
						'static' => 'Celestial\\Module\\DataProvider\\Provider\\StaticDataProvider'
					)
				)
			),
			'hashing' => array(
				'factoryClass' => 'Celestial\\Module\\Hashing\\Factory',
				'options' => array(
					'salt' => 'CelestialDemoHashingSalt'
				)
			),
			'log' => require('Config/Module/log.php'),
			'mysql' => array(
				'factoryClass' => 'Celestial\\Module\\MySql\\Factory',
				'options' => array(
					'host' => $_ENV['DATABASE_HOST'],
					'port' => $_ENV['DATABASE_PORT'],
					'name' => $_ENV['DATABASE_NAME'],
					'password' => $_ENV['DATABASE_PASSWORD'],
					'username' => $_ENV['DATABASE_USER']
				)
			),
			'render' => array(
				'factoryClass' => 'Celestial\\Module\\Render\\Factory',
				'options' => array(
					'viewDirectory' => $this->rootDirectory() . '/Route/View',
					'viewManifestDirectory' => $this->rootDirectory() . '/Route/Manifest',
					'engines' => array(
						'handlebars' => 'Celestial\\Module\\Render\\Engine\\LightNCandy',
						'json' => 'Celestial\\Module\\Render\\Engine\\Json',
						'mustache' => 'Celestial\\Module\\Render\\Engine\\Mustache',
						'php' => 'Celestial\\Module\\Render\\Engine\\Php'
					)
				)
			),
			'request' => array(
				'factoryClass' => 'Celestial\\Module\\Request\\Factory',
				'options' => array()
			),
			'restRender' => array(
				'factoryClass' => 'Celestial\\Module\\Render\\Factory',
				'options' => array(
					'viewDirectory' => $this->rootDirectory() . '/View/Resource',
					'viewManifestDirectory' => $this->rootDirectory() . '/Route/Manifest'
				)
			),
			'restResource' => array(
				'factoryClass' => 'Celestial\\Module\\Data\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'resourceNamespace' => $this->rootNamespace() . '\\Resource',
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest'
				)
			),
			'router' => array(
				'factoryClass' => 'Celestial\\Module\\Router\\Factory',
				'options' => array(
					'routes' => new BaseConfig\Routes(array(
						'auth' => array(
							'namespace' => 'Celestial\\Api\\Authentication'
						),
						'resource' => array(
							'namespace' => 'Celestial\\Api\\Rest\\Controller'
						)
					)),
					'rootNamespace' => $this->rootNamespace(),
					'defaultController' => 'Celestial\\Api\\View\\ViewController'
				)
			),
			'session' => array(
				'factoryClass' => 'Celestial\\Module\\Session\\Factory',
				'options' => array()
			),
			'validation' => array(
				'factoryClass' => 'Celestial\\Module\\Validation\\Factory',
				'options' => array(
					'validators' => array(
						'comparison.contains' => 'Celestial\\Module\\Validation\\Validator\\Comparison\\ContainsValidator',
						'comparison.equal' => 'Celestial\\Module\\Validation\\Validator\\Comparison\\EqualValidator',
						'comparison.unique' => 'Celestial\\Module\\Validation\\Validator\\Comparison\\UniqueValidator',
						'number.greaterThan' => 'Celestial\\Module\\Validation\\Validator\\Number\\GreaterThanValidator',
						'number.integer' => 'Celestial\\Module\\Validation\\Validator\\Number\\IntegerValidator',
						'number.number' => 'Celestial\\Module\\Validation\\Validator\\Number\\NumberValidator',
						'number.lessThan' => 'Celestial\\Module\\Validation\\Validator\\Number\\LessThanValidator',
						'number.maximumDecimalPlaces' => 'Celestial\\Module\\Validation\\Validator\\Number\\MaximumDecimalPlacesValidator',
						'number.maximumDigits' => 'Celestial\\Module\\Validation\\Validator\\Number\\MaximumDigitsValidator',
						'text.text' => 'Celestial\\Module\\Validation\\Validator\\Text\\TextValidator',
						'text.maximumLength' => 'Celestial\\Module\\Validation\\Validator\\Text\\MaximumLengthValidator',
						'text.minimumLength' => 'Celestial\\Module\\Validation\\Validator\\Text\\MinimumLengthValidator'
					)
				)
			)
        ));
    }
}
