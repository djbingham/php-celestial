<?php
namespace Sloth\Api\Rest\Base;

use Sloth\Base\Controller;
use Sloth\Exception;
use Sloth\Face\RequestInterface;
use Sloth\Api\Rest\Face\RestfulParsedRequestInterface;

abstract class RestfulController extends Controller
{
	/**
	 * @param RequestInterface $request
	 * @param string $route
	 * @return RestfulParsedRequestInterface
	 */
	abstract protected function parseRequest(RequestInterface $request, $route);

	/**
	 * @param RestfulParsedRequestInterface $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handleGet(RestfulParsedRequestInterface $request, $route);

	/**
	 * @param RestfulParsedRequestInterface $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handlePost(RestfulParsedRequestInterface $request, $route);

	/**
	 * @param RestfulParsedRequestInterface $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handlePut(RestfulParsedRequestInterface $request, $route);

	/**
	 * @param RestfulParsedRequestInterface $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handleDelete(RestfulParsedRequestInterface $request, $route);

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