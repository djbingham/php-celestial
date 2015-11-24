<?php
namespace Sloth\Module\DataTableQuery\QuerySet\Composer;

use Sloth\Module\DataTableQuery\QuerySet\Base;
use Sloth\Module\DataTableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;

class DeleteComposer extends Base\AbstractComposer
{
	public function compose()
	{
		return new MultiQueryWrapper();
	}
}
