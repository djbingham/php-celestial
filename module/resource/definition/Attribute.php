<?php
namespace Sloth\Module\Resource\Definition;

class Attribute
{
    /**
     * @var string
     */
	private $name;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $fieldName;

    public function __construct(array $properties)
    {
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }
}
