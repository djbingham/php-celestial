<?php

$rootDirectory = dirname(__DIR__);

require str_replace('/', DIRECTORY_SEPARATOR, $rootDirectory . '/vendor/autoload.php');
require str_replace('/', DIRECTORY_SEPARATOR, $rootDirectory . '/Sloth/Utility/Autoload.php');

new Sloth\Utility\Autoload($rootDirectory . DIRECTORY_SEPARATOR . 'Sloth', 'Sloth');
new Sloth\Utility\Autoload($rootDirectory . DIRECTORY_SEPARATOR . 'SlothDemo', 'SlothDemo');
