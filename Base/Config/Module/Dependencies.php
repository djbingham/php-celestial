<?php
namespace Sloth\Base\Config\Module;

class Dependencies
{
	private $modules = array();

	public function __construct(array $dependencies)
	{
		if (array_key_exists('modules', $dependencies)) {
			$this->modules = $dependencies['modules'];
		}
	}

	public function getModules()
	{
		return $this->modules;
	}
}
