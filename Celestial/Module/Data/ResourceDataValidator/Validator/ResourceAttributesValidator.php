<?php
namespace Celestial\Module\Data\ResourceDataValidator\Validator;

use Celestial\Module\Data\Resource\Face\Definition\ResourceInterface;
use Celestial\Module\Data\Resource\Face\ResourceValidatorInterface;
use Celestial\Module\Validation\ValidationModule;

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
		return $this->validationModule->buildValidationResultList();
	}
}
