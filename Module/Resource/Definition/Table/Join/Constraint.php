<?php
namespace Sloth\Module\Resource\Definition\Table\Join;

use Sloth\Module\Resource\Definition\Table\Field;
use Sloth\Module\Resource\Definition\Table\Join;

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
