<?php
namespace DemoGraph\Module\Graph\QueryComponent;

use DemoGraph\Module\Graph\ResourceDefinition\TableField;

class FieldSort
{
    /**
     * @var TableField
     */
    private $field;

    /**
     * @var string
     */
    private $order;

    public function getField()
    {
        return $this->field;
    }

    public function setField(TableField $field)
    {
        $this->field = $field;
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }


}
