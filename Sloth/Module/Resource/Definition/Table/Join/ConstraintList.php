<?php
namespace Sloth\Module\Resource\Definition\Table\Join;

use Sloth\Helper\ObjectList;

class ConstraintList extends ObjectList
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
