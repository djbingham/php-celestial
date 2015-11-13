<?php
namespace Sloth\Module\Resource\Validator;

use Sloth\Module\Resource\Definition\Table\Join;

class TablesUpdateValidator extends Base\TablesValidator
{
	protected function joinRequiresValidation(Join $tableJoin)
	{
		return ($tableJoin->onUpdate === Join::ACTION_UPDATE);
	}
}
