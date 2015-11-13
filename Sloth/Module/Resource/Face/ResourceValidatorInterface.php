<?php
namespace Sloth\Module\Resource\Face;

use Sloth\Module\Resource\Definition;

interface ResourceValidatorInterface
{
	public function validate(Definition\Resource $resourceDefinition, array $attributeValues);
}
