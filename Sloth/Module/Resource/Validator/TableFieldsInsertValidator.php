<?php
namespace Sloth\Module\Resource\Validator;

use Sloth\Module\Resource\Definition\Table\Join;

class TableFieldsInsertValidator extends Base\TableFieldsValidator
{
	protected function joinRequiresValidation(Join $tableJoin)
	{
		return ($tableJoin->onInsert === Join::ACTION_INSERT);
	}

	protected function joinRequiresLinkValidation(Join $tableJoin)
	{
		return ($tableJoin->onUpdate === Join::ACTION_ASSOCIATE);
	}
}
