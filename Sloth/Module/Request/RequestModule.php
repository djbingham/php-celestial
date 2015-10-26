<?php
namespace Sloth\Module\Request;

class RequestModule
{
	public function fromServerVars()
	{
		$requestUri = urldecode($_SERVER['REQUEST_URI']);
		$urlParts = parse_url(urldecode($requestUri));
		if (!array_key_exists('query', $urlParts)) {
			$urlParts['query'] = '';
		}
		if (!array_key_exists('fragment', $urlParts)) {
			$urlParts['fragment'] = '';
		}
		$properties = array(
			'method' => strtolower($_SERVER['REQUEST_METHOD']),
			'uri' => urldecode($requestUri),
			'path' => $urlParts['path'],
			'queryString' => $urlParts['query'],
			'fragment' => $urlParts['fragment'],
			'params' => array(
				'get' => $_GET,
				'post' => $_POST,
				'cookie' => $_COOKIE,
				'server' => $_SERVER
			)
		);
		return $this->fromArray($properties);
	}

	public function fromArray(array $properties)
	{
		return new Request($properties);
	}
}