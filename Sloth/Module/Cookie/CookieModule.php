<?php
namespace Sloth\Module\Cookie;

class CookieModule
{
	public function set($name, $value, $expires = null)
	{
		if (is_null($expires)) {
			setcookie($name, $value);
		} else {
			setcookie($name, $value, $expires);
		}

		return $this;
	}

	public function get($name)
	{
		$value = null;

		if ($this->exists($name)) {
			$value = $_COOKIE[$name];
		}

		return $value;
	}

	public function exists($name)
	{
		return array_key_exists($name, $_COOKIE);
	}

	public function destroy($name)
	{
		if ($this->exists($name)) {
			setcookie($name, '', time() - 3600);
			unset($_COOKIE[$name]);
		}
	}
}
