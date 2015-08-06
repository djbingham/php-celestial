<?php
namespace DemoGraph\Module\Graph\Definition\Table\Join;

use DemoGraph\Module\Graph\Definition\Table;
use DemoGraph\Module\Graph\Definition\Table\Field;

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
    public $parentAttribute;

    /**
     * @var Table
     */
    public $childTable;

    /**
     * @var Field
     */
    public $childAttribute;
}
