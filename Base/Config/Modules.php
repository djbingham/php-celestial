<?php

namespace Sloth\Base\Config;
use Sloth\Exception;

class Modules
{
    private $modules = array();

	public function __construct(array $modules)
	{
        foreach ($modules as $requestPath => $controller) {
            $this->modules[$requestPath] = $controller;
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
        return $this->modules[$name];
    }

	/**
	 * @param string $name
	 * @return bool
	 */
	public function moduleExists($name)
	{
		return array_key_exists($name, $this->modules);
	}
}