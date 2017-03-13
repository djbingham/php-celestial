<?php
return [
	'factoryClass' => 'Celestial\\Module\\Adapter\\Factory',
	'options' => [
		'adapters' => [
			'stringBoolean' => [
				'class' => 'Celestial\\Module\\Adapter\\Adapter\\StringBooleanAdapter',
				'options' => []
			],
			'stringNull' => [
				'class' => 'Celestial\\Module\\Adapter\\Adapter\\StringBooleanAdapter',
				'options' => []
			]
		]
	]
];
