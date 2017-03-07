<?php
return [
	'factoryClass' => 'Celestial\\Module\\Render\\Factory',
	'options' => [
		'viewDirectory' => $this->rootDirectory() . '/Route/View',
		'viewManifestDirectory' => $this->rootDirectory() . '/Route/Manifest',
		'engines' => [
			'handlebars' => 'Celestial\\Module\\Render\\Engine\\LightNCandy',
			'json' => 'Celestial\\Module\\Render\\Engine\\Json',
			'mustache' => 'Celestial\\Module\\Render\\Engine\\Mustache',
			'php' => 'Celestial\\Module\\Render\\Engine\\Php'
		]
	]
];
