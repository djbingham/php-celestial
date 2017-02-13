<?php
namespace Celestial\Module\Data\TableDataValidator\Validator;

use Celestial\Module\Data\Table\Face\JoinInterface;

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
