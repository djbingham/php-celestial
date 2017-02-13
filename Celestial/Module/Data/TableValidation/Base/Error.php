<?php
namespace Celestial\Module\Data\TableValidation\Base;

use Celestial\Module\Validation\Face\ValidationErrorInterface;

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
