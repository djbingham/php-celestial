<?php

namespace Sloth\Base;

abstract class Renderer
{
	/**
	 * @param array $options
	 */
	abstract public function __construct(array $options);

	/**
	 * @param null $view
	 * @param array $parameters
	 */
	abstract public function full($view = null, array $parameters = array());

	/**
	 * @param null $view
	 * @param array $parameters
	 */
	abstract public function partial($view = null, array $parameters = array());

	/**
	 * @param null $view
	 * @param array $parameters
	 * @return string
	 */
	abstract public function captureFull($view = null, array $parameters = array());

	/**
	 * @param null $view
	 * @param array $parameters
	 * @return string
	 */
	abstract public function capturePartial($view = null, array $parameters = array());

    /**
     * @param array $pathParts
     * @return string
     */
    abstract public function createUrl(array $pathParts = array());
}