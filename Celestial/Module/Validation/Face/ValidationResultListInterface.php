<?php
namespace Celestial\Module\Validation\Face;

use Celestial\Helper\Face\ObjectListInterface;

interface ValidationResultListInterface extends ValidationResultInterface, ObjectListInterface
{
	/**
	 * @param ValidationResultInterface $item
	 * @return $this
	 */
	public function pushResult(ValidationResultInterface $item);
}