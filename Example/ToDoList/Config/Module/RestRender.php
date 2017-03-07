<?php
return [
	'factoryClass' => 'Celestial\\Module\\Render\\Factory',
	'options' => [
		'viewDirectory' => $this->rootDirectory() . '/View/Resource',
		'viewManifestDirectory' => $this->rootDirectory() . '/Route/Manifest'
	]
];
