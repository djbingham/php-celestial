<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

class LinkSubJoin
{
    /**
     * @var LinkConstraint
     */
    public $parentJoin;

    /**
     * @var Resource
     */
    public $parentResource;

    /**
     * @var Attribute
     */
    public $parentAttribute;

    /**
     * @var Resource
     */
    public $childResource;

    /**
     * @var Attribute
     */
    public $childAttribute;
}
