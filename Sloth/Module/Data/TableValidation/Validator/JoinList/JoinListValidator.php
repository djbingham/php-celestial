<?php
namespace Sloth\Module\Data\TableValidation\Validator\JoinList;

use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Validation\Face\ValidatorInterface;
use Sloth\Module\Validation\ValidationModule;

class JoinListValidator implements ValidatorInterface
{
	/**
	 * @var DependencyManager
	 */
	private $dependencyManager;

	/**
	 * @var ValidationModule
	 */
	private $validationModule;

	public function __construct(DependencyManager $dependencyManager)
	{
		$this->dependencyManager = $dependencyManager;
		$this->validationModule = $dependencyManager->getValidationModule();
	}

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($fieldList, array $options = array())
	{
		return $this->validationModule->buildValidationResultList();
	}
}
