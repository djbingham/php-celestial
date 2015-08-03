<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Helper\ObjectList;

class LinkConstraintList extends ObjectList
{
    public function push(LinkConstraint $view)
    {
        $this->items[] = $view;
        return $this;
    }

    /**
     * @param string $index
     * @return LinkConstraint
     */
    public function getByIndex($index)
    {
        return parent::getByIndex($index);
    }
}
