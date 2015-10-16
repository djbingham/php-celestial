<?php
namespace Sloth\Module\RestApi;

use Sloth\Module\RestApi\Face\RequestHandlerInterface;
use Sloth\Module\RestApi\Face\RequestParserInterface;
use Sloth\Request;

class RestApiModule
{
	/**
	 * @var RequestParserInterface
	 */
	private $requestParser;

	/**
	 * @var RequestHandlerInterface
	 */
	private $requestHandler;

	public function setRequestParser(RequestParserInterface $requestParser)
	{
		$this->requestParser = $requestParser;
		return $this;
	}

	public function getRequestParser()
	{
		return $this->requestParser;
	}

	public function setRequestHandler(RequestHandlerInterface $requestHandler)
	{
		$this->requestHandler = $requestHandler;
		return $this;
	}

	public function getRequestHandler()
	{
		return $this->requestHandler;
	}

	public function execute(Request $request, $route)
	{
		$parsedRequest = $this->requestParser->parse($request, $route);
		return $this->requestHandler->handle($parsedRequest, $route);
	}
}
