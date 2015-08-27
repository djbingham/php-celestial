<?php
namespace Sloth\Module\Face;

use Sloth\Request;

interface ModuleFactoryInterface
{
	public function __construct(array $dependencies);
	public function initialise();
}