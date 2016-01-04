<?php
namespace Sloth\Module\Data\TableValidation\Base;

use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Validation\Face\ValidationResultInterface;
use Sloth\Module\Validation\ValidationModule;

abstract class BaseFieldPropertyValidator
{
	/**
	 * @var DependencyManager
	 */
	protected $dependencyManager;

	/**
	 * @var ValidationModule
	 */
	protected $validationModule;

	/**
	 * @param string $value
	 * @return ValidationResultInterface
	 */
	abstract public function validate($value);

	public function __construct(DependencyManager $dependencyManager)
	{
		$this->dependencyManager = $dependencyManager;
		$this->validationModule = $dependencyManager->getValidationModule();
	}

	protected function buildValidationError($message)
	{
		return $this->validationModule->buildValidationError(array(
			'message' => $message,
			'validator' => $this
		));
	}
}