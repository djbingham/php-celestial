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
	 * @return string
	 */
	abstract public function defaultController();

    /**
     * @return Config\Modules
     */
    abstract public function modules();

	/**
	 * @return Config\Database
	 */
	abstract public function database();

	/**
	 * @return Config\Routes
	 */
	abstract public function routes();

	/**
	 * @return Initialisation
	 */
	abstract public function initialisation();
}