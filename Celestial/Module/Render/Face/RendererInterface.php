<?php
namespace Celestial\Module\Render\Face;

use Celestial\App;

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
	 * @param string $viewName
	 * @return string
	 */
	public function viewExists($viewName);

	/**
	 * @param string $viewName
	 * @return string
	 */
	public function getView($viewName);

	/**
	 * @param string $viewName
	 * @param array $params
	 * @return string
	 */
	public function renderNamedView($viewName, array $params = array());

	/**
	 * @param ViewInterface $view
	 * @param array $params
	 * @return string
	 */
	public function render(ViewInterface $view, array $params = array());
}
