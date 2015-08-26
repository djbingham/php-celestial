<?php
namespace Sloth\Module\Graph\Test\Mock;

class DatabaseWrapper extends \SlothMySql\DatabaseWrapper
{
	public function escapeString($string)
	{
	   return $string;
	}
}