<?php
namespace Sloth\Module\Resource\DefinitionBuilder;

use Sloth\Module\Resource\Definition;

class AttributeListBuilder
{
	public function build(Definition\Resource $resource, \stdClass $attributesManifest)
	{
		$attributes = new Definition\AttributeList();

		foreach ($attributesManifest as $attributeName => $attributeManifest) {
			if ($attributeManifest instanceof \stdClass) {
				$attribute = $this->build($resource, $attributeManifest);
			} else {
				$attribute = new Definition\Attribute();
			}

			$attribute->name = $attributeName;
			$attribute->resource = $resource;

			$attributes->push($attribute);
		}

		return $attributes;
	}
}
