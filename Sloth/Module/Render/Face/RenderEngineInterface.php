<?php
namespace Sloth\Module\Render\Face;

interface RenderEngineInterface
{
	public function __construct(array $options);

	/**
	 * @param ViewInterface $view
	 * @param array $params
	 * @return string
	 */
	public function render(ViewInterface $view, array $params = []);
}
