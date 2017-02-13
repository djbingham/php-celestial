<?php

namespace Celestial\Base;

use Monolog\Logger;

abstract class Config
{
	/**
	 * @return string
	 */
	abstract public function rootUrl();

	/**
	 * @return string
	 */
	abstract public function rootDirectory();

	/**
	 * @return string
	 */
	abstract public function rootNamespace();

    /**
     * @return Logger
     */
    abstract public function logModule();

    /**
     * @return Config\Modules
     */
    abstract public function modules();
}