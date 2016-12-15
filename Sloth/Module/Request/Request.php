<?php

namespace Sloth\Module\Request;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Request\Face\RequestInterface;

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
	 * @var Params
	 */
	protected $params;

	public function __construct(array $properties)
	{
		$this->validateProperties($properties);

		$properties['params'] = new Params($properties['params']);

		foreach ($properties as $key => $value) {
			$this->$key = $value;
		}

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
            'params' => $this->getParams()->toArray()
        );
    }

	protected function validateProperties(array $properties)
	{
		$required = array('method', 'uri', 'path', 'queryString', 'params');
		$missing = array_diff($required, array_keys($properties));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required properties for Request instance: ' . implode(', ', $missing)
			);
		}

		foreach ($properties as $propertyName => $propertyValue) {
			if (!property_exists($this, $propertyName)) {
				throw new InvalidArgumentException(
					sprintf('Unrecognised property given to Request: %s', $propertyName)
				);
			}
		}

		$stringProperties = array('method', 'uri', 'path', 'queryString');
		foreach ($stringProperties as $propertyName) {
			if (!is_string($properties[$propertyName])) {
				throw new InvalidArgumentException(
					sprintf('Invalid value given to Request instance for property `%s`', $propertyName)
				);
			}
		}

		if (!is_array($properties['params'])) {
			throw new InvalidArgumentException(
				sprintf('Invalid `params` value given to Request instance: %s', json_encode($properties['params']))
			);
		}
	}
}