<?php
namespace Sloth\Module\Validation\Face;

interface ValidatorInterface
{
	/**
	 * @param mixed $input
	 * @param array $options
	 * @return ValidationResultInterface
	 */
	public function validate($input, array $options = array());
}