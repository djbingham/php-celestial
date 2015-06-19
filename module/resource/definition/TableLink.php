<?php
namespace Sloth\Module\Resource\Definition;

class TableLink
{
    /**
     * @var string
     */
	private $parentTable;

    /**
     * @var string
     */
    private $parentField;

    /**
     * @var string
     */
    private $childTable;

    /**
     * @var string
     */
    private $childField;

    public function __construct(array $properties)
    {
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getChildField()
    {
        return $this->childField;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getParentTable()
    {
        return $this->parentTable;
    }

    /**
     * @return string
     */
    public function getParentField()
    {
        return $this->parentField;
    }

    /**
     * @return string
     */
    public function getChildTable()
    {
        return $this->childTable;
    }
}
