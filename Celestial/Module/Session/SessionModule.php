<?php
namespace Celestial\Module\Session;

use Celestial\Exception\InvalidArgumentException;

class SessionModule
{
	public function getId()
	{
		return session_id();
	}

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
