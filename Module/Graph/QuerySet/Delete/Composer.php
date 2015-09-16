<?php
namespace Sloth\Module\Graph\QuerySet\Delete;

use Sloth\Module\Graph\QuerySet\Base;
use Sloth\Module\Graph\QuerySet\MultiQueryWrapper;

class Composer extends Base\AbstractComposer
{
	public function compose()
	{
		return new MultiQueryWrapper();
	}
}
