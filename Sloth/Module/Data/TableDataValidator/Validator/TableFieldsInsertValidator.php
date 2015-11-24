<?php
namespace Sloth\Module\Data\TableDataValidator\Validator;

use Sloth\Module\DataTable\Face\JoinInterface;

class TableFieldsInsertValidator extends Base\TableFieldsValidator
{
	protected function joinRequiresValidation(JoinInterface $tableJoin)
	{
		return ($tableJoin->onInsert === JoinInterface::ACTION_INSERT);
	}

	protected function joinRequiresLinkValidation(JoinInterface $tableJoin)
	{
		return ($tableJoin->onUpdate === JoinInterface::ACTION_ASSOCIATE);
	}
}
