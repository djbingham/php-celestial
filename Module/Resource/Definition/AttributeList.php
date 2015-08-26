<?php
namespace Sloth\Module\Resource\Definition;

class AttributeList
{
	private $attributes = array();
    private $tables = array();

    public function __construct(array $attributes)
    {
        foreach ($attributes as $attributeName => $tableField) {
            if ($tableField instanceof Attribute) {
                $this->attributes[$attributeName] = $tableField;
                if (!in_array($tableField->getTableName(), $this->tables)) {
                    $this->tables[] = $tableField->getTableName();
                }
            } elseif (is_array($tableField)) {
                $this->attributes[$attributeName] = new self($tableField);
            } else {
                list($table, $field) = explode('.', $tableField);
                $this->attributes[$attributeName] = new Attribute(array(
                    'name' => $attributeName,
                    'tableName' => $table,
                    'fieldName' => $field
                ));
                if (!in_array($table, $this->tables)) {
                    $this->tables[] = $table;
                }
            }
        }
    }

    public function append($alias, Attribute $attribute)
    {
        $this->attributes[$alias] = $attribute;
        $table = $attribute->getTableName();
        if (!in_array($table, $this->tables)) {
            $this->tables[] = $table;
        }
        return $this;
    }

    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->attributes;
    }

    /**
     * @param $name
     * @return Attribute
     */
    public function getByName($name)
    {
        return $this->attributes[$name];
    }

    public function contains($name)
    {
        return array_key_exists($name, $this->attributes);
    }
}
