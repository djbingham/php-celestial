<?php
namespace Sloth\Base;

use Sloth\App;

abstract class Initialisation
{
	/**
	 * @return App
	 */
	abstract public function execute();
}
