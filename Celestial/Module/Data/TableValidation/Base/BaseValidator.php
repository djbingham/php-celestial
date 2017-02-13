<?php
namespace Celestial\Module\Data\TableValidation\Base;

use Celestial\Module\Data\TableValidation\DependencyManager;
use Celestial\Module\Validation\Face\ValidationErrorListInterface;
use Celestial\Module\Validation\Face\ValidatorInterface;
use Celestial\Module\Validation\ValidationModule;

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
