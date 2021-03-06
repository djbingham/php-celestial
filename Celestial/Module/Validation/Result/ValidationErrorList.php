<?php
namespace Celestial\Module\Validation\Result;

use Celestial\Helper\ObjectListTrait;
use Celestial\Module\Validation\Face\ValidationErrorInterface;
use Celestial\Module\Validation\Face\ValidationErrorListInterface;

class ValidationErrorList implements ValidationErrorListInterface
{
	use ObjectListTrait;

	public function push(ValidationErrorInterface $item)
	{
		$this->append($item);
		return $this;
	}

	public function merge(ValidationErrorListInterface $errorList)
	{
		foreach ($errorList as $error) {
			$this->append($error);
		}

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
