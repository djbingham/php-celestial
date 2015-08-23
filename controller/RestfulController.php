<?php
namespace Sloth\Controller;

use Sloth\Base\Controller;
use Sloth\Exception;
use Sloth\Module\Graph\RequestParser\RestfulParsedRequest;
use Sloth\Request;

abstract class RestfulController extends Controller
{
	/**
	 * @param Request $request
	 * @param string $route
	 * @return RestfulParsedRequest
	 */
	abstract protected function parseRequest(Request $request, $route);

	/**
	 * @param RestfulParsedRequest $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handleGet(RestfulParsedRequest $request, $route);

	/**
	 * @param RestfulParsedRequest $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handlePost(RestfulParsedRequest $request, $route);

	/**
	 * @param RestfulParsedRequest $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handlePut(RestfulParsedRequest $request, $route);

	/**
	 * @param RestfulParsedRequest $request
	 * @param string $route
	 * @return string
	 */
	abstract protected function handleDelete(RestfulParsedRequest $request, $route);

	/**
	 * @param Request $request
	 * @param string $route
	 * @return string
	 * @throws Exception\InvalidRequestException
	 */
    public function execute(Request $request, $route)
    {
		$parsedRequest = $this->parseRequest($request, $route);
		$method = 'handle' . ucfirst($parsedRequest->getMethod());

		if (!method_exists($this, $method)) {
			throw new Exception\InvalidRequestException(sprintf('Method not found: %s', $method));
		}

		return $this->$method($parsedRequest, $route);
    }
}