<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

class TableField
{
    /**
     * @var Table
     */
    public $table;

    /**
     * @var string
     */
	public $name;

    /**
     * @var string
     */
    public $alias;

    public function getAlias()
    {
        $alias = null;
        if ($this->alias !== null) {
            $alias = $this->alias;
        } else {
            $alias = $this->name;
        }
        return $alias;
    }
}
