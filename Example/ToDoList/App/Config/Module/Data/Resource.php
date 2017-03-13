<?php
return [
	'factoryClass' => 'Celestial\\Module\\Data\\Resource\\Factory',
	'options' => [
		'resourceManifestDirectory' => $this->rootDirectory() . '/Schema/Resource',
		'resourceNamespace' => $this->rootNamespace() . '\\Resource'
	]
];
