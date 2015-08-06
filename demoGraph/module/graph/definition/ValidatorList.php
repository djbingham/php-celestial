<?php
namespace DemoGraph\Module\Graph\Definition;

use DemoGraph\Module\Graph\Helper\ObjectList;

class ValidatorList extends ObjectList
{
    public function push(Validator $validator)
    {
        $this->items[] = $validator;
        return $this;
    }

    /**
     * @param string $index
     * @return Validator
     */
    public function getByIndex($index)
    {
        return parent::getByIndex($index);
    }
}
