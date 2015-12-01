<?php
namespace Sloth\Module\Data\Resource\DefinitionBuilder;

use Sloth\Module\Data\Resource\Definition;
use Sloth\Module\Data\Resource\Face\Definition\ResourceInterface;

class AttributeListBuilder
{
	public function build(ResourceInterface $resource, \stdClass $attributesManifest)
	{
		$attributes = new Definition\Resource\AttributeList();

		foreach ($attributesManifest as $attributeName => $attributeManifest) {
			if ($attributeManifest instanceof \stdClass) {
				$attribute = $this->build($resource, $attributeManifest);
			} else {
				$attribute = new Definition\Resource\Attribute();
			}

			$attribute->name = $attributeName;
			$attribute->resource = $resource;

			$attributes->push($attribute);
		}

		return $attributes;
	}
}
