<?php
namespace Sloth\Module\Session;

use Sloth\Exception\InvalidArgumentException;

class SessionModule
{
	public function exists($name)
	{
		return array_key_exists($name, $_SESSION);
	}

	public function get($name)
	{
		if (!$this->exists($name)) {
			throw new InvalidArgumentException(
				sprintf('Failed to find item in session data: `%s`', $name)
			);
		}
		return $_SESSION[$name];
	}

	public function set($name, $value)
	{
		$_SESSION[$name] = $value;
		return $this;
	}

	public function remove($name)
	{
		unset($_SESSION[$name]);
	}

	public function destroy()
	{
		session_destroy();
	}
}
