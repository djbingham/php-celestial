<?php
namespace Sloth\Face;

use Sloth\App;
use Sloth\Request;

interface ModuleFactoryInterface
{
	public function __construct(App $app, array $options);
	public function initialise();
}