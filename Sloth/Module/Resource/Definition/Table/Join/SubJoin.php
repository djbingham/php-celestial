<?php
namespace Sloth\Module\Resource\Definition\Table\Join;

use Sloth\Module\Resource\Definition\Table;
use Sloth\Module\Resource\Definition\Table\Field;

class SubJoin
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
