<?php
namespace Sloth\Module\Data\TableQuery\Test\Mock;

class DatabaseWrapper extends \SlothMySql\DatabaseWrapper
{
	public function escapeString($string)
	{
	   return $string;
	}
}