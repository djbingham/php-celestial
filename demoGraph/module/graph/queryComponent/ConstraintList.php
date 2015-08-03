<?php
namespace DemoGraph\Module\Graph\QueryComponent;

use DemoGraph\Module\Graph\Helper\ObjectList;

class ConstraintList extends ObjectList
{
    public function push(Constraint $attribute)
    {
        $this->items[] = $attribute;
        return $this;
    }

    /**
     * @return Constraint
     */
    public function shift()
    {
        return array_shift($this->items);
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
