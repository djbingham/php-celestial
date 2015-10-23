<?php

namespace Sloth\Base\Config;
use Helper\Face\ObjectListInterface;
use Sloth\Base\Config\Module\Module;
use Sloth\Exception;
use Sloth\Helper\ObjectListTrait;

class Modules implements ObjectListInterface
{
	use ObjectListTrait;

	public function __construct(array $modules)
	{
		foreach ($modules as $moduleName => $moduleConfig) {
			$moduleConfig['name'] = $moduleName;
			$this->items[] = new Module($moduleConfig);
		}
	}

	/**
	 * @param string $name
	 * @return string
	 * @throws Exception\InvalidArgumentException
	 */
	public function get($name)
	{
		if (!array_key_exists($name, $this->modules)) {
			throw new Exception\InvalidArgumentException(
				sprintf('Unrecognised module requested: %s', $name)
			);
		}
		return $this->items[$name];
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function moduleExists($name)
	{
		return array_key_exists($name, $this->items);
	}
}