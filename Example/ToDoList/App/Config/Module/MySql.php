<?php
return [
	'factoryClass' => 'Celestial\\Module\\MySql\\Factory',
	'options' => [
		'host' => $_ENV['DATABASE_HOST'],
		'port' => $_ENV['DATABASE_PORT'],
		'name' => $_ENV['DATABASE_NAME'],
		'password' => $_ENV['DATABASE_PASSWORD'],
		'username' => $_ENV['DATABASE_USER']
	]
];
