<?php
namespace Sloth\Module\Validation\Result;

use Sloth\Helper\ObjectListTrait;
use Sloth\Module\Validation\Face\ValidationErrorInterface;
use Sloth\Module\Validation\Face\ValidationErrorListInterface;

class ValidationErrorList implements ValidationErrorListInterface
{
	use ObjectListTrait;

	public function push(ValidationErrorInterface $item)
	{
		$this->append($item);
		return $this;
	}

	public function getMessages()
	{
		$messages = array();

		/** @var ValidationErrorInterface $item */
		foreach ($this as $item) {
			$messages[] = $item->getMessage();
		}

		return $messages;
	}
}
