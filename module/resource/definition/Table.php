<?php
namespace Sloth\Module\Resource\Definition;

class Table
{
    /**
     * @var string
     */
	private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $autoIncrement;

    /**
     * @var string
     */
    private $primaryKey;

    /**
     * @var array
     */
    private $attributes = array();

    /**
     * @var array
     */
    private $links = array();

    /**
     * @var Boolean
     */
    private $primary;

    /**
     * @var Boolean
     */
    private $editable;

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

    public function getType()
    {
        return $this->type;
    }

    public function getAutoIncrement()
    {
        return $this->autoIncrement;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getLinksToParent()
    {
        return $this->links;
    }

    public function isPrimary()
    {
        return $this->primary;
    }

    public function isEditable()
    {
        return $this->editable;
    }

}
