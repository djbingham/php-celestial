<?php
namespace Sloth\Module\Graph\Definition\Table\Join;

use Sloth\Module\Graph\Definition\Table;
use Sloth\Module\Graph\Definition\Table\Field;

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
