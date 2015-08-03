<?php
namespace DemoGraph\Module\Graph\QueryComponent;

use DemoGraph\Module\Graph\Helper\ObjectList;

class FieldSortList extends ObjectList
{
    public function push(FieldSort $attribute)
    {
        $this->items[] = $attribute;
        return $this;
    }

    /**
     * @param string $index
     * @return FieldSort
     */
    public function getByIndex($index)
    {
        return parent::getByIndex($index);
    }
}
