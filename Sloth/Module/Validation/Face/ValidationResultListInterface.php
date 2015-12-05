<?php
namespace Sloth\Module\Validation\Face;

use Helper\Face\ObjectListInterface;

interface ValidationResultListInterface extends ValidationResultInterface, ObjectListInterface
{
	/**
	 * @param ValidationResultInterface $item
	 * @return $this
	 */
	public function pushResult(ValidationResultInterface $item);
}