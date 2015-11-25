<?php
namespace Sloth\Module\Data\TableDataValidator\Validator;

use Sloth\Module\Data\Table\Face\JoinInterface;

class TableFieldsUpdateValidator extends Base\TableFieldsValidator
{
	protected function joinRequiresValidation(JoinInterface $tableJoin)
	{
		return ($tableJoin->onUpdate === JoinInterface::ACTION_UPDATE);
	}

	protected function joinRequiresLinkValidation(JoinInterface $tableJoin)
	{
		return ($tableJoin->onUpdate === JoinInterface::ACTION_ASSOCIATE);
	}
}
