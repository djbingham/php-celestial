<?php
namespace Sloth\Module\Validation\Face;

use Helper\Face\ObjectListInterface;

interface ValidationErrorListInterface extends ObjectListInterface
{
	/**
	 * @param ValidationErrorInterface $item
	 * @return $this
	 */
	public function push(ValidationErrorInterface $item);

	/**
	 * @return array
	 */
	public function getMessages();
}