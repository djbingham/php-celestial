<?php
namespace Sloth\Module\Data\ResourceDataValidator\Validator;

use Sloth\Module\Data\Resource\Face\Definition\ResourceInterface;
use Sloth\Module\Data\Resource\Face\ResourceValidatorInterface;
use Sloth\Module\Validation\ValidationModule;

class ResourceAttributesValidator implements ResourceValidatorInterface
{
	/**
	 * @var ValidationModule
	 */
	private $validationModule;

	public function __construct(ValidationModule $validationModule)
	{
		$this->validationModule = $validationModule;
	}

	public function validate(ResourceInterface $resourceDefinition, array $attributeValues)
	{
		return true;
	}
}
