<?php
return [
	'factoryClass' => 'Celestial\\Module\\DataProvider\\Factory',
	'options' => [
		'providers' => [
			'authentication' => 'Celestial\\Module\\DataProvider\\Provider\\AuthenticationDataProvider',
			'request.getProperty' => 'Celestial\\Module\\DataProvider\\Provider\\Request\\GetParameterDataProvider',
			'request' => 'Celestial\\Module\\DataProvider\\Provider\\RequestProvider',
			'resource' => 'Celestial\\Module\\DataProvider\\Provider\\ResourceProvider',
			'resourceList' => 'Celestial\\Module\\DataProvider\\Provider\\ResourceListProvider',
			'session' => 'Celestial\\Module\\DataProvider\\Provider\\SessionDataProvider',
			'static' => 'Celestial\\Module\\DataProvider\\Provider\\StaticDataProvider'
		]
	]
];
