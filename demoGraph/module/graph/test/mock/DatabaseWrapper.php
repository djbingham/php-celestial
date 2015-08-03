<?php
namespace DemoGraph\Module\Graph\Test\Mock;

class DatabaseWrapper extends \SlothMySql\DatabaseWrapper
{
	public function escapeString($string)
	{
	   return $string;
	}
}