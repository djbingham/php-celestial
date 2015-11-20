<?php
namespace Sloth\Module\Resource\Validator;

use Sloth\Module\Resource\Definition\Table\Join;

class TableFieldsUpdateValidator extends Base\TableFieldsValidator
{
	protected function joinRequiresValidation(Join $tableJoin)
	{
		return ($tableJoin->onUpdate === Join::ACTION_UPDATE);
	}

	protected function joinRequiresLinkValidation(Join $tableJoin)
	{
		return ($tableJoin->onUpdate === Join::ACTION_ASSOCIATE);
	}
}
