<?php
namespace Celestial\Module\Data\TableDataValidator\Validator;

use Celestial\Module\Data\Table\Face\JoinInterface;

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
