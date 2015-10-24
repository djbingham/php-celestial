<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

new Sloth\Utility\Autoload(__DIR__, 'SlothDemo');

$init = new \SlothDemo\Initialisation();
$init->execute();
