<?php
namespace Sloth\Module\Data\Resource\Test\Mock;

class DatabaseWrapper extends \SlothMySql\DatabaseWrapper
{
	public function escapeString($string)
	{
	   return $string;
	}
}