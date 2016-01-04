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
	 * @param ValidationErrorListInterface $errors
	 * @return $this
	 */
	public function pushErrors(ValidationErrorListInterface $errors);

	/**
	 * @return ValidationErrorListInterface
	 */
	public function getErrors();

	/**
	 * @return ValidatorInterface
	 */
	public function getValidator();
}
