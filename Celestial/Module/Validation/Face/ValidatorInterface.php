<?php
namespace Celestial\Module\Validation\Face;

interface ValidatorInterface
{
	/**
	 * @param mixed $validatorList
	 * @param array $options
	 * @return ValidationResultInterface
	 */
	public function validate($validatorList, array $options = array());

	/**
	 * @param array $options
	 * @return ValidationResultInterface
	 */
	public function validateOptions(array $options);
}