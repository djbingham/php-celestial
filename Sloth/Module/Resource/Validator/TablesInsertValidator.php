<?php
namespace Sloth\Module\Resource\Validator;

use Sloth\Module\Resource\Definition\Table\Join;

class TablesInsertValidator extends Base\TablesValidator
{
	protected function joinRequiresValidation(Join $tableJoin)
	{
		return ($tableJoin->onInsert === Join::ACTION_INSERT);
	}
}
