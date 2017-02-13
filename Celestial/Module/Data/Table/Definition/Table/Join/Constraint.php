<?php
namespace Celestial\Module\Data\Table\Definition\Table\Join;

use Celestial\Module\Data\Table\Definition\Table\Field;
use Celestial\Module\Data\Table\Definition\Table\Join;
use Celestial\Module\Data\Table\Face\ConstraintInterface;

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
