<?php

namespace Sloth\Base;

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
     * @return Config\Modules
     */
    abstract public function modules();
}