<?php
namespace Celestial\Module\Data\Resource\Face;

interface ResourceValidatorInterface
{
	public function validate(Definition\ResourceInterface $resourceDefinition, array $attributeValues);
}
