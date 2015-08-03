<?php
namespace DemoGraph\Module\Graph\QueryComponent;

class Field
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $alias;

    public function getTable()
    {
        return $this->table;
    }

    public function setTable(Table $table)
    {
        $this->table = $table;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }
}
