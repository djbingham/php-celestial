<?php
namespace Sloth\Module\Data\Table\Definition\Table\Join;

use Sloth\Module\Data\Table\Definition\Table;
use Sloth\Module\Data\Table\Definition\Table\Field;
use Sloth\Module\Data\Table\Face\SubJoinInterface;

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
