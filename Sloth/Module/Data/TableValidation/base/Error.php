<?php
namespace Sloth\Module\Data\TableValidation\Base;

use Sloth\Module\Validation\Face\ValidationErrorInterface;

class Error implements ValidationErrorInterface
{
	private $children;

	public function pushChild(Error $childError)
	{

	}

	public function getChildren()
	{
		return $this->children;
	}
}
