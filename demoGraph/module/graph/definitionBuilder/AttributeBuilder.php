<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\ResourceDefinition;

class AttributeBuilder
{
    /**
     * @var ValidatorListBuilder
     */
    private $validatorListBuilder;

    /**
     * @var array
     */
    private $cache = array();

    public function __construct(ValidatorListBuilder $validatorListBuilder)
    {
        $this->validatorListBuilder = $validatorListBuilder;
    }

    public function build(ResourceDefinition\Resource $resource, array $attributeManifest)
    {
        $attribute = $this->getCachedAttribute($resource->alias, $attributeManifest['name']);
        if (is_null($attribute)) {
            $attribute = new ResourceDefinition\Attribute();
            $attribute->resource = $resource;
            $attribute->table = $resource->table;
            $attribute->name = $attributeManifest['name'];
            $attribute->field = $this->buildTableField($resource->table, $attributeManifest['field']);
            $attribute->type = $attributeManifest['type'];
            $validatorManifest = array_key_exists('validators', $attributeManifest) ? $attributeManifest['validators'] : array();
            $attribute->validators = $this->validatorListBuilder->build($resource, $validatorManifest);
            $this->cacheAttribute($attribute);
        }
        return $attribute;
    }

    private function buildTableField(ResourceDefinition\Table $table, $fieldName)
    {
        $field = new ResourceDefinition\TableField();
        $field->table = $table;
        $field->name = $fieldName;
        $field->alias = sprintf('%s.%s', $table->getAlias(), $fieldName);
        return $field;
    }

    private function cacheAttribute(ResourceDefinition\Attribute $attribute)
    {
        $resourceName = $attribute->resource->name;
        $this->cache[$resourceName][$attribute->name] = $attribute;
        return $this;
    }

    /**
     * @param string $resourceName
     * @param string $attributeName
     * @return ResourceDefinition\Attribute
     */
    private function getCachedAttribute($resourceName, $attributeName)
    {
        if (!array_key_exists($resourceName, $this->cache)) {
            return null;
        }
        if (!array_key_exists($attributeName, $this->cache[$resourceName])) {
            return null;
        }
        return $this->cache[$resourceName][$attributeName];
    }
}
