<?php
namespace Sloth\Base;

use SlothMySql;
use Sloth\App;
use Sloth\Utility;

abstract class Initialisation
{
	/**
	 * @return App
	 */
	abstract public function getApp();

	/**
	 * @return SlothMySql\DatabaseWrapper
	 */
	abstract public function getDatabase();

	/**
	 * @return Router
	 */
	abstract public function getRouter();

	/**
	 * @return Renderer
	 */
	abstract public function getRenderer();

	/**
	 * @var Config
	 */
	protected $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}
}
