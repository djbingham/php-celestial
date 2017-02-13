<?php
namespace Celestial\Module\Data\Table\Definition\Table;

use Celestial\Helper\ObjectList;
use Celestial\Module\Data\Table\Face\ValidatorListInterface;

class ValidatorList extends ObjectList implements ValidatorListInterface
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
