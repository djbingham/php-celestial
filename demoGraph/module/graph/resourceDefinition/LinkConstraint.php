<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

class LinkConstraint
{
    /**
     * @var Link
     */
    public $link;

    /**
     * @var Attribute
     */
    public $parentAttribute;

    /**
     * @var Attribute
     */
    public $childAttribute;

    /**
     * @var LinkSubJoinList
     */
    public $subJoins;
}
