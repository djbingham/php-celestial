<?php
namespace Sloth\Controller;

use Sloth\Base\Controller;
use Sloth\Exception;
use Sloth\Face\RequestInterface;
use Sloth\Module\RestApi\Face\ParsedRequestInterface;

abstract class RestfulController extends Controller
{
	/**
	 * @param RequestInterface $request
	 * @param string $route
	 * @return ParsedRequestInterface
	 */
	abstract protected function parseRequest(RequestInterface $request, $route);

	/**
	 * @param ParsedRequestInterface $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handleGet(ParsedRequestInterface $request, $route);

	/**
	 * @param ParsedRequestInterface $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handlePost(ParsedRequestInterface $request, $route);

	/**
	 * @param ParsedRequestInterface $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handlePut(ParsedRequestInterface $request, $route);

	/**
	 * @param ParsedRequestInterface $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handleDelete(ParsedRequestInterface $request, $route);

	/**
	 * @param RequestInterface $request
	 * @param string $route
	 * @return string
	 * @throws Exception\InvalidRequestException
	 */
    public function execute(RequestInterface $request, $route)
    {
		$parsedRequest = $this->parseRequest($request, $route);
		$method = 'handle' . ucfirst($parsedRequest->getMethod());

		if (!method_exists($this, $method)) {
			throw new Exception\InvalidRequestException(sprintf('Method not found: %s', $method));
		}

		return $this->$method($parsedRequest, $route);
    }
}