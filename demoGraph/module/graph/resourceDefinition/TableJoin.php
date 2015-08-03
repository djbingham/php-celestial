<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

class TableJoin
{
    /**
     * @var LinkConstraint
     */
    public $parentJoin;

    /**
     * @var Table
     */
    public $parentTable;

    /**
     * @var TableField
     */
    public $parentField;

    /**
     * @var Table
     */
    public $childTable;

    /**
     * @var TableField
     */
    public $childField;
}
