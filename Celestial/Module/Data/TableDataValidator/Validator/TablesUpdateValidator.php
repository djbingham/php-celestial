<?php
namespace Celestial\Module\Data\TableDataValidator\Validator;

use Celestial\Module\Data\Table\Face\JoinInterface;

class TablesUpdateValidator extends Base\TablesValidator
{
	protected function joinRequiresValidation(JoinInterface $tableJoin)
	{
		return ($tableJoin->onUpdate === JoinInterface::ACTION_UPDATE);
	}
}
