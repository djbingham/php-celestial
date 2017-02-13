<?php
namespace Celestial\Base;

use Celestial\App;
use Celestial\Exception\InvalidArgumentException;
use Celestial\Face\ModuleFactoryInterface;

abstract class AbstractModuleFactory implements ModuleFactoryInterface
{
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @return mixed
	 */
	abstract public function initialise();

	/**
	 * @return void
	 * @throws InvalidArgumentException
	 */
	abstract protected function validateOptions();

	public function __construct(App $app, array $options = array())
	{
		$this->app = $app;
		$this->options = $options;
		$this->validateOptions();
	}
}
