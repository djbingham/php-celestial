<?php
namespace Sloth\Module\Graph\DefinitionBuilder;

use Sloth\Module\Graph\Definition;

class TableFieldBuilder
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

    public function build(Definition\Table $table, array $attributeManifest)
    {
        $attribute = $this->getCachedAttribute($table->alias, $attributeManifest['name']);
        if (is_null($attribute)) {
            $attribute = new Definition\Table\Field();
            $attribute->table = $table;
            $attribute->name = $attributeManifest['name'];
            $attribute->alias = sprintf('%s.%s', $table->getAlias(), $attributeManifest['field']);
            $attribute->type = $attributeManifest['type'];
            $validatorManifest = array_key_exists('validators', $attributeManifest) ? $attributeManifest['validators'] : array();
            $attribute->validators = $this->validatorListBuilder->build($validatorManifest);
            $this->cacheAttribute($attribute);
        }
        return $attribute;
    }

    private function cacheAttribute(Definition\Table\Field $attribute)
    {
        $tableName = $attribute->table->name;
        $this->cache[$tableName][$attribute->name] = $attribute;
        return $this;
    }

    /**
     * @param string $tableName
     * @param string $attributeName
     * @return \Sloth\Module\Graph\Definition\Table\Field
     */
    private function getCachedAttribute($tableName, $attributeName)
    {
        if (!array_key_exists($tableName, $this->cache)) {
            return null;
        }
        if (!array_key_exists($attributeName, $this->cache[$tableName])) {
            return null;
        }
        return $this->cache[$tableName][$attributeName];
    }
}
