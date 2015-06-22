<?php
namespace Sloth\Module\Resource\Base;

use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Definition\Table;
use Sloth\Module\Resource\Definition\TableList;

interface ResourceDefinition
{
    /**
     * @return string
     */
    public function name();

    /**
     * @return AttributeList
     */
    public function attributeList();

	/**
	 * @return string
	 */
	public function autoAttribute();

    /**
     * @return string
     */
    public function primaryAttribute();

    /**
     * @return string
     */
    public function resourceClass();

    /**
     * @return string
     */
    public function factoryClass();

    /**
     * @return array
     */
    public function views();

    /**
     * @param string $name
     * @return string
     */
    public function view($name);

    /**
     * @return TableList
     */
    public function tableList();

    /**
     * @return array
     */
    public function tableSelectOrder();

    /**
     * @return Table
     */
    public function primaryTable();
}
