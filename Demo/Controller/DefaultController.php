<?php
namespace Sloth\Demo\Controller;

use Sloth\Base\Controller\RestfulController;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Api\Rest\Face\ParsedRequestInterface;
use Sloth\Api\Rest\ParsedRequest;

class DefaultController extends RestfulController
{
	public function parseRequest(RequestInterface $request, $route)
	{
		$requestProperties = $request->toArray();
		return new ParsedRequest($requestProperties);
	}

	public function handleGet(ParsedRequestInterface $request, $route)
	{
		$renderer = $this->getRenderModule();

		$viewName = trim(preg_replace(sprintf('/^%s/', $route), '', $request->getPath()), '/');
		$view = $renderer->getViewFactory()->getByName($viewName);

		return $renderer->render($view);
	}

	public function handlePost(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot post to resource/index');
	}

	public function handlePut(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot put to resource/index');
	}

	public function handleDelete(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot delete from resource/index');
	}

	/**
	 * @return RendererInterface
	 */
	private function getRenderModule()
	{
		return $this->module('render');
	}
}
