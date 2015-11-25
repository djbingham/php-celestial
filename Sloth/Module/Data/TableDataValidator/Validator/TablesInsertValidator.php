<?php
namespace Sloth\Module\Data\TableDataValidator\Validator;

use Sloth\Module\Data\Table\Face\JoinInterface;

class TablesInsertValidator extends Base\TablesValidator
{
	protected function joinRequiresValidation(JoinInterface $tableJoin)
	{
		return ($tableJoin->onInsert === JoinInterface::ACTION_INSERT);
	}
}
