<?php
namespace Sloth\Module\Data\Resource\Test\Mock;

class DatabaseWrapper extends \PhpMySql\DatabaseWrapper
{
	public function escapeString($string)
	{
	   return $string;
	}
}