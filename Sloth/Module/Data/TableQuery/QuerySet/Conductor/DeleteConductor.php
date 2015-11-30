<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Conductor;

use Sloth\Module\Data\TableQuery\QuerySet\Base;
use Sloth\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;

class DeleteConductor extends Base\AbstractConductor
{
	public function conduct()
	{
		/** @var SingleQueryWrapperInterface $queryWrapper */
		foreach ($this->querySetToExecute as $queryWrapper) {
			$query = $queryWrapper->getQuery();
			$this->database->execute($query);
		}

		return array();
	}
}
