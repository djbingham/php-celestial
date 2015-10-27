<?php
namespace Sloth\Module\Session;

class SessionModule
{
	public function exists($name)
	{
		return array_key_exists($name, $_SESSION);
	}

	public function get($name)
	{
		return $_SESSION[$name];
	}

	public function set($name, $value)
	{
		$_SESSION[$name] = $value;
		return $this;
	}

	public function destroy($name)
	{
		unset($_SESSION[$name]);
	}
}
