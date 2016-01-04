<?php
namespace Sloth\Module\Validation\Face;

interface ValidatorInterface
{
	/**
	 * @param mixed $fieldList
	 * @param array $options
	 * @return ValidationResultInterface
	 */
	public function validate($fieldList, array $options = array());

	/**
	 * @param array $options
	 * @return ValidationResultInterface
	 */
	public function validateOptions(array $options);
}