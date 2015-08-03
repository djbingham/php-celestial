<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\ResourceDefinition;

class AttributeListBuilder
{
    /**
     * @var AttributeBuilder
     */
    private $attributeBuilder;

    public function __construct(AttributeBuilder $attributeBuilder)
    {
        $this->attributeBuilder = $attributeBuilder;
    }

    public function build(ResourceDefinition\Resource $resource, array $attributesManifest)
    {
        $attributes = new ResourceDefinition\AttributeList();
        foreach ($attributesManifest as $attributeName => $attributeManifest) {
            $attributeManifest['name'] = $attributeName;
            $attributes->push($this->attributeBuilder->build($resource, $attributeManifest));
        }
        return $attributes;
    }
}
