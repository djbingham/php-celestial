<?php
return [
	'factoryClass' => 'Celestial\\Module\\Data\\Resource\\Factory',
	'options' => [
		'resourceManifestDirectory' => $this->rootDirectory() . '/Schema/Resource',
		'resourceNamespace' => $this->rootNamespace() . '\\Resource',
		'tableManifestDirectory' => $this->rootDirectory() . '/Schema/Table'
	]
];
