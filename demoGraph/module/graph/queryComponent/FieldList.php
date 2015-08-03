<?php
namespace DemoGraph\Module\Graph\QueryComponent;

use DemoGraph\Module\Graph\Helper\ObjectList;

class FieldList extends ObjectList
{
    public function push(Field $attribute)
    {
        $this->items[] = $attribute;
        return $this;
    }

    /**
     * @param string $index
     * @return Field
     */
    public function getByIndex($index)
    {
        return parent::getByIndex($index);
    }
}
