<?php
return [
	'factoryClass' => 'Celestial\\Module\\Render\\Factory',
	'options' => [
		'viewDirectory' => $this->rootDirectory() . '/View/Template',
		'viewManifestDirectory' => $this->rootDirectory() . '/View/Manifest'
	]
];
