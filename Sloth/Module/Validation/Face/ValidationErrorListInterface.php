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
	 * @param ValidationErrorListInterface $errorList
	 * @return $this
	 */
	public function merge(ValidationErrorListInterface $errorList);

	/**
	 * @return array
	 */
	public function getMessages();
}