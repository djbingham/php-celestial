<?php
namespace Sloth\Demo\Module\Resource;

class Factory extends \Sloth\Module\Resource\Factory
{
	protected function validateDependencies(array $dependencies)
	{
		return true;
	}
}
