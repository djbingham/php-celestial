<?php
namespace Sloth\Module\Graph\QuerySet\Composer;

use Sloth\Module\Graph\QuerySet\Base;
use Sloth\Module\Graph\QuerySet\QueryWrapper\MultiQueryWrapper;

class DeleteComposer extends Base\AbstractComposer
{
	public function compose()
	{
		return new MultiQueryWrapper();
	}
}
