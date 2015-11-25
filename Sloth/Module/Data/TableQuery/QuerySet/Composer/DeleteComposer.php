<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Composer;

use Sloth\Module\Data\TableQuery\QuerySet\Base;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;

class DeleteComposer extends Base\AbstractComposer
{
	public function compose()
	{
		return new MultiQueryWrapper();
	}
}
