<?php
namespace Celestial\Module\Hashing;

class HashingModule
{
	/**
	 * @var string
	 */
	protected $salt;

	/**
	 * @var string
	 */
	protected $defaultAlgorithm = 'sha256';

	public function setSalt($salt)
	{
		$this->salt = $salt;

		return $this;
	}

	public function setDefaultAlgorithm($algorithm)
	{
		$this->defaultAlgorithm = $algorithm;

		return $this;
	}

	public function insecureHash($value, $algorithm = null)
	{
		$string = $this->salt . $value;

		if ($algorithm === null) {
			$algorithm = $this->defaultAlgorithm;
		}

		return hash($algorithm, $string);
	}

	public function secureHash($value)
	{
		return password_hash($value, PASSWORD_DEFAULT);
	}

	public function verifySecureHash($value, $hash)
	{
		return password_verify($value, $hash);
	}
}
