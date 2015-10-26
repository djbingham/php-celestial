<?php
namespace Sloth\Module\Request;

use Sloth\Exception\InvalidArgumentException;

class Params
{
	/**
	 * @var array
	 */
	private $get = array();

	/**
	 * @var array
	 */
	private $post = array();

	/**
	 * @var array
	 */
	private $cookie = array();

	/**
	 * @var array
	 */
	private $session = array();

	/**
	 * @var array
	 */
	private $server = array();

	public function __construct(array $properties)
	{
		$this->validateProperties($properties);
		foreach ($properties as $type => $propertiesOfType) {
			$this->$type = $propertiesOfType;
		}
	}

	public function get()
	{
		return $this->get;
	}

	public function post()
	{
		return $this->post;
	}

	public function cookie()
	{
		return $this->cookie;
	}

	public function session()
	{
		return $this->session;
	}

	public function server()
	{
		return $this->server;
	}

	public function toArray()
	{
		return array(
			'get' => $this->get(),
			'post' => $this->post(),
			'cookie' => $this->cookie(),
			'session' => $this->session(),
			'server' => $this->server()
		);
	}

	protected function validateProperties(array $properties)
	{
		foreach ($properties as $type => $propertiesOfType) {
			if (!property_exists($this, $type)) {
				throw new InvalidArgumentException(
					sprintf('Request contains params of unknown type: %s', $type)
				);
			}
			if (!is_array($propertiesOfType)) {
				throw new InvalidArgumentException(
					sprintf('Request params should be in an array. Non-array given: %s', print_r($propertiesOfType, true))
				);
			}
		}
	}
}
