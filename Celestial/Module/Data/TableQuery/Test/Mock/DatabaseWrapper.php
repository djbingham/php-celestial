<?php
namespace Celestial\Module\Data\TableQuery\Test\Mock;

class DatabaseWrapper extends \PhpMySql\DatabaseWrapper
{
	public function escapeString($string)
	{
	   return $string;
	}
}