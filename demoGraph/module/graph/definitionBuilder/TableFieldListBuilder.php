<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\Definition;

class TableFieldListBuilder
{
    /**
     * @var TableFieldBuilder
     */
    private $attributeBuilder;

    public function __construct(TableFieldBuilder $attributeBuilder)
    {
        $this->attributeBuilder = $attributeBuilder;
    }

    public function build(Definition\Table $table, array $attributesManifest)
    {
        $attributes = new Definition\Table\FieldList();
        foreach ($attributesManifest as $attributeName => $attributeManifest) {
            $attributeManifest['name'] = $attributeName;
            $attributes->push($this->attributeBuilder->build($table, $attributeManifest));
        }
        return $attributes;
    }
}
