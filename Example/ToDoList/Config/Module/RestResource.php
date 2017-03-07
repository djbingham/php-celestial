<?php
return [
	'factoryClass' => 'Celestial\\Module\\Data\\Resource\\Factory',
	'options' => [
		'resourceManifestDirectory' => $this->rootDirectory() . '/Resource/ResourceManifest',
		'resourceNamespace' => $this->rootNamespace() . '\\Resource',
		'tableManifestDirectory' => $this->rootDirectory() . '/Resource/TableManifest'
	]
];
