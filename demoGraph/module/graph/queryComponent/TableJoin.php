<?php
namespace DemoGraph\Module\Graph\QueryComponent;

class TableJoin
{
    /**
     * @var Table
     */
    private $parentTable;

    /**
     * @var Table
     */
    private $childTable;

    /**
     * @var ConstraintList
     */
    private $constraintList;

    public function setParentTable(Table $tableDefinition)
    {
        $this->parentTable = $tableDefinition;
        return $this;
    }

    public function setChildTable(Table $tableDefinition)
    {
        $this->childTable = $tableDefinition;
        return $this;
    }

    public function getParentTable()
    {
        return $this->parentTable;
    }

    public function getChildTable()
    {
        return $this->childTable;
    }

    public function setConstraints(ConstraintList $joinList)
    {
        $this->constraintList = $joinList;
        return $this;
    }

    public function getConstraints()
    {
        return $this->constraintList;
    }
}
