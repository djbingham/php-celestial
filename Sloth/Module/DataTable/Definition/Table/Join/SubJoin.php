<?php
namespace Sloth\Module\DataTable\Definition\Table\Join;

use Sloth\Module\DataTable\Definition\Table;
use Sloth\Module\DataTable\Definition\Table\Field;
use Sloth\Module\DataTable\Face\SubJoinInterface;

class SubJoin implements SubJoinInterface
{
    /**
     * @var Constraint
     */
    public $parentJoin;

    /**
     * @var Table
     */
    public $parentTable;

    /**
     * @var Field
     */
    public $parentField;

    /**
     * @var Table
     */
    public $childTable;

    /**
     * @var Field
     */
    public $childField;
}
