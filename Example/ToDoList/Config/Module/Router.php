<?php
return [
	'factoryClass' => 'Celestial\\Module\\Router\\Factory',
	'options' => [
		'routes' => new \Celestial\Base\Config\Routes([
			'auth' => [
				'namespace' => 'Celestial\\Api\\Authentication'
			],
			'resource' => [
				'namespace' => 'Celestial\\Api\\Rest\\Controller'
			]
		]),
		'rootNamespace' => $this->rootNamespace(),
		'defaultController' => 'Celestial\\Api\\View\\ViewController'
	]
];
