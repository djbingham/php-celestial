<?php

namespace Sloth\Module\Request;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Face\RequestInterface;

class Request implements RequestInterface
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
	 * @var Params
	 */
	protected $params;

	public function __construct(array $properties)
	{
		$this->method = $properties['method'];
		$this->uri = $properties['uri'];
		$this->path = $properties['path'];
		$this->queryString = $properties['queryString'];
		$this->fragment = $properties['fragment'];
		$this->params = new Params(array(
			'get' => $properties['params']['get'],
			'post' => $properties['params']['post'],
			'cookie' => $properties['params']['cookie'],
			'server' => $properties['params']['server']
		));
		return $this;
	}

	public function __get($name)
	{
		if (!property_exists($this, $name)) {
			throw new InvalidArgumentException(
				sprintf('Unrecognised Request property requested: %s', $name)
			);
		}
		return $this->$name;
	}

	public function canBeCached()
	{
		return ($this->getMethod() === 'get');
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getUri()
	{
		return $this->uri;
	}

	public function getPath()
	{
		return trim($this->path, '/');
	}

	public function getQueryString()
	{
		return $this->queryString;
	}

	public function getFragment()
	{
		return $this->fragment;
	}

	public function getParams()
	{
		return $this->params;
	}

    public function toArray()
    {
        return array(
            'method' => $this->getMethod(),
            'uri' => $this->getUri(),
            'path' => $this->getPath(),
            'queryString' => $this->getQueryString(),
            'fragment' => $this->getFragment(),
            'params' => array(
                'get' => $this->getParams()->get(),
                'post' => $this->getParams()->post(),
                'cookie' => $this->getParams()->cookie(),
                'server' => $this->getParams()->server(),
            )
        );
    }
}