<?php
namespace Celestial\Module\Data\Resource\Test\Mock;

class DatabaseWrapper extends \PhpMySql\DatabaseWrapper
{
	public function escapeString($string)
	{
	   return $string;
	}
}