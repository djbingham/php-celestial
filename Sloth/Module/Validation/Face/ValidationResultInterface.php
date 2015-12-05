<?php
namespace Sloth\Module\Validation\Face;

interface ValidationResultInterface
{
	/**
	 * @return boolean
	 */
	public function isValid();

	/**
	 * @param ValidationErrorInterface $error
	 * @return $this
	 */
	public function pushError(ValidationErrorInterface $error);

	/**
	 * @return ValidationErrorListInterface
	 */
	public function getErrors();

	/**
	 * @return ValidatorInterface
	 */
	public function getValidator();
}
