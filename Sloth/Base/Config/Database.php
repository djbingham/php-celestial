<?php

namespace Sloth\Base\Config;


class Database
{
    private $name;
	private $username;
	private $password;
	private $host;
	private $port;
	private $socket;

	public function __construct(array $details)
	{
		$this->fromArray($details);
		$this->validateProperties();
	}

	public function name()
	{
		return $this->name;
	}

	public function username()
	{
		return $this->username;
	}

	public function password()
	{
		return $this->password;
	}

	public function host()
	{
		return $this->host;
	}

	public function port()
	{
		return $this->port;
	}

	public function socket()
	{
		return $this->socket;
	}

	public function toArray()
	{
		return array(
			'name' => $this->name,
			'username' => $this->username,
			'password' => $this->password,
			'host' => $this->host,
			'port' => $this->port,
			'socket' => $this->socket
		);
	}

	private function fromArray(array $details)
	{
		$this->name = $this->extractFromArray('name', $details);
		$this->username = $this->extractFromArray('username', $details);
		$this->password = $this->extractFromArray('password', $details);
		$this->host = $this->extractFromArray('host', $details);
		$this->port = $this->extractFromArray('port', $details);
		$this->socket = $this->extractFromArray('socket', $details);
		return $this;
	}

	private function extractFromArray($key, array $config)
	{
		$configItem = null;
		if (array_key_exists($key, $config)) {
			$configItem = $config[$key];
		}
		return $configItem;
	}

	private function validateProperties()
	{
		return $this;
	}
}