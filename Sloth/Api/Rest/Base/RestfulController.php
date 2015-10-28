<?php
namespace Sloth\Api\Rest\Base;

use Sloth\Base\Controller;
use Sloth\Exception;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Api\Rest\Face\RestfulParsedRequestInterface;

abstract class RestfulController extends Controller
{
	/**
	 * @param RoutedRequestInterface $request
	 * @return RestfulParsedRequestInterface
	 */
	abstract protected function parseRequest(RoutedRequestInterface $request);

	/**
	 * @param RestfulParsedRequestInterface $request
	 * @return string
	 */
	abstract protected function handleGet(RestfulParsedRequestInterface $request);

	/**
	 * @param RestfulParsedRequestInterface $request
	 * @return string
	 */
	abstract protected function handlePost(RestfulParsedRequestInterface $request);

	/**
	 * @param RestfulParsedRequestInterface $request
	 * @return string
	 */
	abstract protected function handlePut(RestfulParsedRequestInterface $request);

	/**
	 * @param RestfulParsedRequestInterface $request
	 * @return string
	 */
	abstract protected function handleDelete(RestfulParsedRequestInterface $request);

	/**
	 * @param RoutedRequestInterface $request
	 * @return string
	 * @throws Exception\InvalidRequestException
	 */
    public function execute(RoutedRequestInterface $request)
    {
		$parsedRequest = $this->parseRequest($request);
		$method = 'handle' . ucfirst($parsedRequest->getMethod());

		if (!method_exists($this, $method)) {
			throw new Exception\InvalidRequestException(sprintf('Method not found: %s', $method));
		}

		return $this->$method($parsedRequest);
    }
}
