<?php
namespace Sloth\Module\Data\TableValidation\Base;

use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Validation\Face\ValidationErrorListInterface;
use Sloth\Module\Validation\Face\ValidatorInterface;
use Sloth\Module\Validation\ValidationModule;

abstract class BaseValidator implements ValidatorInterface
{
	/**
	 * @var ValidationModule
	 */
	protected $validationModule;

	public function __construct(DependencyManager $dependencyManager)
	{
		$this->validationModule = $dependencyManager->getValidationModule();
	}

	protected function buildError($message, ValidationErrorListInterface $children = null)
	{
		return $this->validationModule->buildValidationError(array(
			'validator' => $this,
			'message' => $message,
			'children' => $children
		));
	}
}
