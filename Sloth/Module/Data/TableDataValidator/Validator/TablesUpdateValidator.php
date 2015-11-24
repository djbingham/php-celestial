<?php
namespace Sloth\Module\Data\TableDataValidator\Validator;

use Sloth\Module\DataTable\Face\JoinInterface;

class TablesUpdateValidator extends Base\TablesValidator
{
	protected function joinRequiresValidation(JoinInterface $tableJoin)
	{
		return ($tableJoin->onUpdate === JoinInterface::ACTION_UPDATE);
	}
}
