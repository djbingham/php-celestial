<?php
namespace Sloth\Module\Data\TableValidation\Face;

use Sloth\Module\Validation\Face\ValidationResultInterface;

interface FieldPropertyValidatorInterface
{
	/**
	 * @param string $value
	 * @param string $fieldName
	 * @return ValidationResultInterface
	 */
	public function validate($value, $fieldName);
}