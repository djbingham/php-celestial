<?php
namespace Sloth\Module\Resource\QuerySet\Composer;

use Sloth\Module\Resource\QuerySet\Base;
use Sloth\Module\Resource\QuerySet\QueryWrapper\MultiQueryWrapper;

class DeleteComposer extends Base\AbstractComposer
{
	public function compose()
	{
		return new MultiQueryWrapper();
	}
}
