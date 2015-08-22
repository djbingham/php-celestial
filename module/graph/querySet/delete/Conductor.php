<?php
namespace Sloth\Module\Graph\QuerySet\Delete;

use Sloth\Module\Graph\QuerySet\Base;
use Sloth\Module\Graph\QuerySet\DataParser;
use Sloth\Module\Graph\QuerySet\QuerySet;
use Sloth\Module\Graph\QuerySet\QuerySetItem;
use Sloth\Module\Graph\Definition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Query\Constraint;
use SlothMySql\QueryBuilder\Query\Select;

class Conductor extends Base\Conductor
{
	public function conduct()
	{
		return array();
	}
}
