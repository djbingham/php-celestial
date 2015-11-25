<?php
namespace Sloth\Module\Data\Table\Definition\Table\Join;

use Sloth\Helper\ObjectList;
use Sloth\Module\Data\Table\Face\ConstraintListInterface;

class ConstraintList extends ObjectList implements ConstraintListInterface
{
    public function push(Constraint $view)
    {
        $this->items[] = $view;
        return $this;
    }

    /**
     * @param string $index
     * @return Constraint
     */
    public function getByIndex($index)
    {
        return parent::getByIndex($index);
    }
}
