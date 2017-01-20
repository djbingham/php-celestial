<?php
return [
	'factoryClass' => 'Sloth\\Module\\Log\\Factory',
	'options' => [
		// Monolog-Cascade config
		'version' => 1,
		'formatters' => [
			'spaced' => [
				'format' => "%datetime% %channel%.%level_name%  %message%\n",
				'include_stacktraces' => true
			],
			'dashed' => [
				'format' => "%datetime%-%channel%.%level_name% - %message%\n"
			],
		],
		'handlers' => [
			'console' => [
				'class' => 'Monolog\Handler\StreamHandler',
				'level' => 'DEBUG',
				'formatter' => 'spaced',
				'stream' => 'php://stdout'
			],

			'info_file_handler' => [
				'class' => 'Monolog\Handler\StreamHandler',
				'level' => 'INFO',
				'formatter' => 'dashed',
				'stream' => '../../log/info.log'
			],

			'error_file_handler' => [
				'class' => 'Monolog\Handler\StreamHandler',
				'level' => 'ERROR',
				'stream' => '../../log/error.log',
				'formatter' => 'spaced'
			]
		],
		'processors' => [
			'tag_processor' => [
				'class' => 'Monolog\Processor\TagProcessor'
			]
		],
		'loggers' => [
			'default' => [
				'handlers' => ['console', 'info_file_handler', 'error_file_handler']
			]
		]
	]
];
