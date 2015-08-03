<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\ResourceDefinition\Resource as GraphResource;

class Attribute
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var GraphResource
     */
	public $resource;

    /**
     * @var Table
     */
    public $table;

    /**
     * @var TableField
     */
    public $field;

    /**
     * @var ValidatorList
     */
    public $validators = array();
}
