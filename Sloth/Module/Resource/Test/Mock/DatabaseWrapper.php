<?php
namespace Sloth\Module\Resource\Test\Mock;

class DatabaseWrapper extends \SlothMySql\DatabaseWrapper
{
	public function escapeString($string)
	{
	   return $string;
	}
}