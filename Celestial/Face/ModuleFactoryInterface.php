<?php
namespace Celestial\Face;

use Celestial\App;

interface ModuleFactoryInterface
{
	public function __construct(App $app, array $options);
	public function initialise();
}