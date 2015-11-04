<?php
namespace Sloth\Module\Hashing;

class HashingModule
{
	/**
	 * @var string
	 */
	protected $salt;

	/**
	 * @var string
	 */
	protected $algorithm = 'md5';

	public function setSalt($salt)
	{
		$this->salt = $salt;
	}

	public function setAlgorithm($algorithm)
	{
		$this->algorithm = $algorithm;
		return $this;
	}

	public function hash($value)
	{
		$function = $this->algorithm;
		return $function($value . $this->salt);
	}
}
