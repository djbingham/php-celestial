<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Helper\ObjectList;

class LinkSubJoinList extends ObjectList
{
    public function push(LinkSubJoin $view)
    {
        $this->items[] = $view;
        return $this;
    }

    /**
     * @return LinkSubJoin
     */
    public function shift()
    {
        $item = array_shift($this->items);
        return $item;
    }

    /**
     * @param string $index
     * @return LinkSubJoin
     */
    public function getByIndex($index)
    {
        return parent::getByIndex($index);
    }

    public function getByParentResourceAlias($parentAlias)
    {
        $foundJoin = null;
        foreach ($this as $join) {
            /** @var LinkSubJoin $join */
            if ($join->parentResource->getAlias() === $parentAlias) {
                $foundJoin = $join;
            }
        }
        return $foundJoin;
    }
}
