<?php
namespace Celestial\Module\Data\Table\Definition\Table\Join;

use Celestial\Module\Data\Table\Definition\Table;
use Celestial\Module\Data\Table\Definition\Table\Field;
use Celestial\Module\Data\Table\Face\SubJoinInterface;

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
