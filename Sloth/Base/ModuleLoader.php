<?php
namespace Sloth\Base;

use Sloth\Module;

class ModuleLoader
{
	// @todo: Implement a module loader that can be passed into all controllers,
	// enabling them to access modules via dependency injection

	public function resource()
	{
		return new Module\Data\Resource\Loader();
	}
}
