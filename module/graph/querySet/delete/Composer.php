<?php
namespace Sloth\Module\Graph\QuerySet\Delete;

use Sloth\Module\Graph\QuerySet\Base;
use Sloth\Module\Graph\QuerySet\Filter;
use Sloth\Module\Graph\QuerySet\QuerySet;
use Sloth\Module\Graph\QuerySet\QuerySetItem;
use Sloth\Module\Graph\Definition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\Abstractory\Value\ATable as QueryTable;

class Composer extends Base\Composer
{
	public function compose()
	{
		return new QuerySet();;
	}
}
