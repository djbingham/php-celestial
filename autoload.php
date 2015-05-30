<?php

/**
 * Initialise autoload for the framework
 */
require __DIR__ . '/vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require __DIR__ . '/utility' . DIRECTORY_SEPARATOR . 'Autoload.php';

new Sloth\Utility\Autoload(__DIR__, 'Sloth');
new Sloth\Utility\Autoload(__DIR__ . '/slothDefault', 'SlothDefault');
