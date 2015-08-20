<?php

namespace Sloth;

use Sloth\Exception\InvalidArgumentException;

class Request
{
	/**
	 * @var string
	 */
	protected $method;

	/**
	 * @var string
	 */
	protected $uri;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $queryString;

	/**
	 * @var string
	 */
	protected $fragment;

	/**
	 * @var Request\Params
	 */
	protected $params;

	public function __get($name)
	{
		if (!property_exists($this, $name)) {
			throw new InvalidArgumentException(
				sprintf('Unrecognised Request property requested: %s', $name)
			);
		}
		return $this->$name;
	}

	public static function fromServerVars()
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
		return self::fromArray($properties);
	}

    public static function fromArray(array $properties)
    {
        $instance = new self();
        $instance->method = $properties['method'];
        $instance->uri = $properties['uri'];
        $instance->path = $properties['path'];
        $instance->queryString = $properties['queryString'];
        $instance->fragment = $properties['fragment'];
        $instance->params = new Request\Params(array(
            'get' => $properties['params']['get'],
            'post' => $properties['params']['post'],
            'cookie' => $properties['params']['cookie'],
            'server' => $properties['params']['server']
        ));
        return $instance;
    }

	public function method()
	{
		return $this->method;
	}

	public function uri()
	{
		return $this->uri;
	}

	public function path()
	{
		return rtrim($this->path, '/');
	}

	public function queryString()
	{
		return $this->queryString;
	}

	public function fragment()
	{
		return $this->fragment;
	}

	public function params()
	{
		return $this->params;
	}

    public function toArray()
    {
        return array(
            'method' => $this->method(),
            'uri' => $this->uri(),
            'path' => $this->path(),
            'queryString' => $this->queryString(),
            'fragment' => $this->fragment(),
            'params' => array(
                'get' => $this->params()->get(),
                'post' => $this->params()->post(),
                'cookie' => $this->params()->cookie(),
                'server' => $this->params()->server(),
            )
        );
    }
}