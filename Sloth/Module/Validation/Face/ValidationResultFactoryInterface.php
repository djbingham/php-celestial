<?php
namespace Sloth\Module\Validation\Face;

interface ValidationResultFactoryInterface
{
	/**
	 * @param array $properties
	 * @return ValidationResultInterface
	 */
	public function buildResult(array $properties);

	/**
	 * @param array $results
	 * @return ValidationResultListInterface
	 */
	public function buildResultList(array $results);

	/**
	 * @param array $properties
	 * @return ValidationErrorInterface
	 */
	public function buildError(array $properties);

	/**
	 * @param array $errors
	 * @return ValidationErrorListInterface
	 */
	public function buildErrorList(array $errors);
}
