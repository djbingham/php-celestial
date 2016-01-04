<?php
namespace Sloth\Module\Validation\Face;

interface ValidationErrorInterface
{
	/**
	 * @return string
	 */
	public function getMessage();

	/**
	 * @return ValidatorInterface
	 */
	public function getValidator();

	/**
	 * @return ValidationErrorListInterface
	 */
	public function getChildren();
}