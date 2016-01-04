<?php
namespace Sloth\Face;

use Sloth\App;

interface ModuleFactoryInterface
{
	public function __construct(App $app, array $options);
	public function initialise();
}