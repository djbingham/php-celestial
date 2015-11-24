<?php
namespace Sloth\Module\DataTable\Definition\Table\Join;

use Sloth\Module\DataTable\Definition\Table\Field;
use Sloth\Module\DataTable\Definition\Table\Join;
use Sloth\Module\DataTable\Face\ConstraintInterface;

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
