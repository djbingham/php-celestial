<?php
namespace Sloth\Module\Resource\Validator;
use Sloth\Module\Resource\Definition;
use Sloth\Module\Resource\Face\ResourceValidatorInterface;
use Sloth\Module\Validation\ValidationModule;

class TablesValidator implements ResourceValidatorInterface
{
	/**
	 * @var ValidationModule
	 */
	private $validationModule;

	public function __construct(ValidationModule $validationModule)
	{
		$this->validationModule = $validationModule;
	}

	public function validate(Definition\Resource $resourceDefinition, array $attributeValues)
	{
		return true;
	}
}
