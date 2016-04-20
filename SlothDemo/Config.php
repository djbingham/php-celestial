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
			'data.resource' => array(
				'factoryClass' => 'Sloth\\Module\\Data\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'resourceNamespace' => $this->rootNamespace() . '\\Resource'
				)
			),
			'data.resourceDataValidator' => array(
				'factoryClass' => 'Sloth\\Module\\Data\\ResourceDataValidator\\Factory',
				'options' => array()
			),
			'data.table' => array(
				'factoryClass' => 'Sloth\\Module\\Data\\Table\\Factory',
				'options' => array(
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest'
				)
			),
			'data.tableValidation' => array(
				'factoryClass' => 'Sloth\\Module\\Data\\TableValidation\\Factory',
				'options' => array()
			),
			'data.tableDataValidator' => array(
				'factoryClass' => 'Sloth\\Module\\Data\\TableDataValidator\\Factory',
				'options' => array()
			),
			'data.tableQuery' => array(
				'factoryClass' => 'Sloth\\Module\\Data\\TableQuery\\Factory',
				'options' => array()
			),
			'dataProvider' => array(
				'factoryClass' => 'Sloth\\Module\\DataProvider\\Factory',
				'options' => array(
					'providers' => array(
						'authentication' => 'Sloth\\Module\\DataProvider\\Provider\\AuthenticationDataProvider',
						'request.getProperty' => 'Sloth\\Module\\DataProvider\\Provider\\Request\\GetPropertyDataProvider',
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
			'restRender' => array(
				'factoryClass' => 'Sloth\\Module\\Render\\Factory',
				'options' => array(
					'viewDirectory' => $this->rootDirectory() . '/View/Resource',
					'viewManifestDirectory' => null
				)
			),
			'restResource' => array(
				'factoryClass' => 'Sloth\\Module\\Data\\Resource\\Factory',
				'options' => array(
					'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
					'resourceNamespace' => $this->rootNamespace() . '\\Resource',
					'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest'
				)
			),
			'router' => array(
				'factoryClass' => 'Sloth\\Module\\Router\\Factory',
				'options' => array(
					'routes' => new BaseConfig\Routes(array(
						'auth' => array(
							'namespace' => 'Sloth\\Api\\Authentication'
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
			),
			'validation' => array(
				'factoryClass' => 'Sloth\\Module\\Validation\\Factory',
				'options' => array(
					'validators' => array(
						'comparison.contains' => 'Sloth\\Module\\Validation\\Validator\\Comparison\\ContainsValidator',
						'comparison.equal' => 'Sloth\\Module\\Validation\\Validator\\Comparison\\EqualValidator',
						'comparison.unique' => 'Sloth\\Module\\Validation\\Validator\\Comparison\\UniqueValidator',
						'number.greaterThan' => 'Sloth\\Module\\Validation\\Validator\\Number\\GreaterThanValidator',
						'number.integer' => 'Sloth\\Module\\Validation\\Validator\\Number\\IntegerValidator',
						'number.number' => 'Sloth\\Module\\Validation\\Validator\\Number\\NumberValidator',
						'number.lessThan' => 'Sloth\\Module\\Validation\\Validator\\Number\\LessThanValidator',
						'number.maximumDecimalPlaces' => 'Sloth\\Module\\Validation\\Validator\\Number\\MaximumDecimalPlacesValidator',
						'number.maximumDigits' => 'Sloth\\Module\\Validation\\Validator\\Number\\MaximumDigitsValidator',
						'text.text' => 'Sloth\\Module\\Validation\\Validator\\Text\\TextValidator',
						'text.maximumLength' => 'Sloth\\Module\\Validation\\Validator\\Text\\MaximumLengthValidator',
						'text.minimumLength' => 'Sloth\\Module\\Validation\\Validator\\Text\\MinimumLengthValidator'
					)
				)
			)
        ));
    }
}
