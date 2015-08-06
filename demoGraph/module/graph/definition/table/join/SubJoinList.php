<?php
namespace DemoGraph\Module\Graph\Definition\Table\Join;

use DemoGraph\Module\Graph\Helper\ObjectList;

class SubJoinList extends ObjectList
{
    public function push(SubJoin $view)
    {
        $this->items[] = $view;
        return $this;
    }

    /**
     * @return SubJoin
     */
    public function shift()
    {
        $item = array_shift($this->items);
        return $item;
    }

    /**
     * @param string $index
     * @return SubJoin
     */
    public function getByIndex($index)
    {
        return parent::getByIndex($index);
    }

    public function getByParentTableAlias($parentAlias)
    {
        $foundJoin = null;
        foreach ($this as $join) {
            /** @var SubJoin $join */
            if ($join->parentTable->getAlias() === $parentAlias) {
                $foundJoin = $join;
            }
        }
        return $foundJoin;
    }
}
