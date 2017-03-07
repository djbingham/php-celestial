<?php
return [
	'factoryClass' => 'Celestial\\Module\\Log\\Factory',
	'options' => [
		// Monolog-Cascade config
		'version' => 1,
		'formatters' => [
			'with_context' => [
				'class' => 'Bramus\Monolog\Formatter\ColoredLineFormatter',
				'format' => "%datetime% %channel%.%level_name%  %message%  %context%\n",
				'include_stacktraces' => true
			],
			'with_message_source' => [
				'class' => 'Bramus\Monolog\Formatter\ColoredLineFormatter',
				'format' => "%datetime% %channel%.%level_name%  %message%  (source: %context.logSource%)\n"
			],
			'without_context' => [
				'class' => 'Bramus\Monolog\Formatter\ColoredLineFormatter',
				'format' => "%datetime% %channel%.%level_name%  %message%\n"
			]
		],
		'handlers' => [
			'console' => [
				'class' => 'Monolog\Handler\StreamHandler',
				'level' => 'INFO',
				'formatter' => 'with_message_source',
				'stream' => 'php://stdout'
			],

			'debug_file' => [
				'class' => 'Monolog\Handler\StreamHandler',
				'level' => 'DEBUG',
				'stream' => '../../log/debug.log',
				'formatter' => 'with_context'
			],

			'error_file' => [
				'class' => 'Monolog\Handler\StreamHandler',
				'level' => 'ERROR',
				'stream' => '../../log/error.log',
				'formatter' => 'with_message_source'
			]
		],
		'processors' => [
			'tag_processor' => [
				'class' => 'Monolog\Processor\TagProcessor'
			]
		],
		'loggers' => [
			'default' => [
				'handlers' => ['console', 'debug_file', 'error_file']
			]
		]
	]
];
