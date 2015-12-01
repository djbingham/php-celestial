<?php
namespace Sloth\Module\Data\Resource\Definition\Resource;

use Sloth\Helper\ObjectList;

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