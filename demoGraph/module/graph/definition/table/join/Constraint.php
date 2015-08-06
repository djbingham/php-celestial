<?php
namespace DemoGraph\Module\Graph\Definition\Table\Join;

use DemoGraph\Module\Graph\Definition\Table\Field;
use DemoGraph\Module\Graph\Definition\Table\Join;

class Constraint
{
    /**
     * @var Join
     */
    public $link;

    /**
     * @var Field
     */
    public $parentAttribute;

    /**
     * @var Field
     */
    public $childAttribute;

    /**
     * @var SubJoinList
     */
    public $subJoins;
}
