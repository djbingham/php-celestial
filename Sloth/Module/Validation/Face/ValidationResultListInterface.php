<?php
namespace Sloth\Module\Validation\Face;

use Sloth\Helper\Face\ObjectListInterface;

interface ValidationResultListInterface extends ValidationResultInterface, ObjectListInterface
{
	/**
	 * @param ValidationResultInterface $item
	 * @return $this
	 */
	public function pushResult(ValidationResultInterface $item);
}