<?php
namespace Sloth\Module\Data\Table\Definition\Table\Join;

use Sloth\Module\Data\Table\Definition\Table\Field;
use Sloth\Module\Data\Table\Definition\Table\Join;
use Sloth\Module\Data\Table\Face\ConstraintInterface;

class Constraint implements ConstraintInterface
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
