<?php
namespace Sloth\Module\Graph\Definition\Table\Join;

use Sloth\Module\Graph\Definition\Table\Field;
use Sloth\Module\Graph\Definition\Table\Join;

class Constraint
{
    /**
     * @var Join
     */
    public $link;

    /**
     * @var Field
     */
    public $parentField;

    /**
     * @var Field
     */
    public $childField;

    /**
     * @var SubJoinList
     */
    public $subJoins;
}
