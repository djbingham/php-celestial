<?php
namespace Sloth\Module\Data\TableDataValidator\Validator;

use Sloth\Module\DataTable\Face\JoinInterface;

class TablesInsertValidator extends Base\TablesValidator
{
	protected function joinRequiresValidation(JoinInterface $tableJoin)
	{
		return ($tableJoin->onInsert === JoinInterface::ACTION_INSERT);
	}
}
