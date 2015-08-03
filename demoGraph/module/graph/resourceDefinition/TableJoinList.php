<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Helper\ObjectList;

class TableJoinList extends ObjectList
{
    public function push(TableJoin $view)
    {
        $this->items[] = $view;
        return $this;
    }

    /**
     * @return TableJoin
     */
    public function shift()
    {
        $item = array_shift($this->items);
        return $item;
    }

    /**
     * @param string $index
     * @return TableJoin
     */
    public function getByIndex($index)
    {
        return parent::getByIndex($index);
    }

    public function getByParentTableAlias($parentAlias)
    {
        $foundJoin = null;
        foreach ($this as $join) {
            /** @var TableJoin $join */
            if ($join->parentTable->getAlias() === $parentAlias) {
                $foundJoin = $join;
            }
        }
        return $foundJoin;
    }
}
