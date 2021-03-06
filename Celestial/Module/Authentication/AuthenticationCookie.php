<?php
namespace Celestial\Module\Authentication;

use Celestial\Exception\InvalidArgumentException;

class AuthenticationCookie
{
	/**
	 * @var string
	 */
	private $identifier;

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var \DateTime
	 */
	private $expires;

	public function __construct(array $properties)
	{
		$this->validateProperties($properties);
		$properties = $this->formatProperties($properties);

		foreach ($properties as $propertyName => $propertyValue) {
			$this->$propertyName = $propertyValue;
		}
	}

	public function getIdentifier()
	{
		return $this->identifier;
	}

	public function getToken()
	{
		return $this->token;
	}

	public function setToken($token)
	{
		$this->token = $token;
		return $this;
	}

	public function getExpires()
	{
		return $this->expires;
	}

	public function setExpires($expires)
	{
		$this->expires = $expires;
		return $this;
	}

	public function toArray()
	{
		return array(
			'identifier' => $this->identifier,
			'token' => $this->token,
			'expires' => $this->expires->format('Y-m-d H:i:s')
		);
	}

	private function validateProperties(array $properties)
	{
		$expected = array('identifier', 'token', 'expires');
		$given = array_keys($properties);

		$missing = array_diff($expected, $given);
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Required properties not given to AuthenticationCookie: ' . implode(', ', $missing)
			);
		}

		$unexpected = array_diff($given, $expected);
		if (!empty($unexpected)) {
			throw new InvalidArgumentException(
				'Unexpected properties passed to AuthenticationCookie: ' . implode(', ', $unexpected)
			);
		}
	}

	private function formatProperties(array $properties)
	{
		if (!($properties['expires'] instanceof \DateTime)) {
			$properties['expires'] = new \DateTime($properties['expires']);
		}

		return $properties;
	}
}
