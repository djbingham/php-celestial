<?php
namespace Sloth\Module\Resource\Definition;

class AttributeList
{
	private $attributes = array();

    public function __construct(array $attributes)
    {
        foreach ($attributes as $attributeName => $tableField) {
            if (is_array($tableField)) {
                $this->attributes[$attributeName] = new self($tableField);
            } else {
                list($table, $field) = explode('.', $tableField);
                $this->attributes[$attributeName] = new Attribute(array(
                    'name' => $attributeName,
                    'tableName' => $table,
                    'fieldName' => $field
                ));
            }
        }
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
}
