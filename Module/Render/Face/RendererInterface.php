<?php
namespace Sloth\Module\Render\Face;

use Sloth\App;

interface RendererInterface
{
	/**
	 * @param App $app
	 * @return $this
	 */
	public function setApp(App $app);

	/**
	 * @return App
	 */
	public function getApp();

	/**
	 * @param ViewFactoryInterface $viewFactory
	 * @return $this
	 */
	public function setViewFactory(ViewFactoryInterface $viewFactory);

	/**
	 * @return ViewFactoryInterface
	 */
	public function getViewFactory();

	/**
	 * @param ViewInterface $view
	 * @param array $params
	 * @return string
	 */
	public function render(ViewInterface $view, array $params = array());
}
