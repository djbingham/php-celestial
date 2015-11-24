<?php
namespace Sloth\Module\Resource\Face;

interface ResourceValidatorInterface
{
	public function validate(Definition\ResourceInterface $resourceDefinition, array $attributeValues);
}
