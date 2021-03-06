<?php
namespace Celestial\Module\Request;

use Celestial\Helper\InternalCacheTrait;

class RequestModule
{
	use InternalCacheTrait;

	/**
	 * @return Request
	 */
	public function fromServerVars()
	{
		if (!$this->isCached('fromServerVars')) {
			$requestUri = urldecode($_SERVER['REQUEST_URI']);

			$urlParts = parse_url(urldecode($requestUri));
			if (!array_key_exists('query', $urlParts)) {
				$urlParts['query'] = '';
			}

			$properties = array(
				'method' => strtolower($_SERVER['REQUEST_METHOD']),
				'protocol' => strtolower(isset($_SERVER['HTTPS'])) && $_SERVER['HTTPS'] !== 'Off' ? 'https' : 'http',
				'uri' => urldecode($requestUri),
				'path' => $urlParts['path'],
				'queryString' => $urlParts['query'],
				'params' => array(
					'get' => $_GET,
					'post' => $_POST,
					'cookie' => $_COOKIE,
					'server' => $_SERVER
				),
				'sessionId' => session_id()
			);
			$request = $this->buildRequest($properties);
			$this->setCached('fromServerVars', $request);
		}
		return $this->getCached('fromServerVars');
	}

	public function buildRequest(array $properties)
	{
		return new Request($properties);
	}

	public function buildRoutedRequest(array $properties)
	{
		return new RoutedRequest($properties);
	}
}
