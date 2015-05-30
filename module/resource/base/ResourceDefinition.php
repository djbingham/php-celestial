<?php
namespace Sloth\Module\Resource\Base;

interface ResourceDefinition
{
    /**
     * @return string
     */
    public function name();

    /**
     * @return array
     */
    public function attributes();

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
     * @return array
     */
    public function tables();

    /**
     * @return array
     */
    public function tableNames();

    /**
     * @param string $name
     * @return array
     */
    public function table($name);

    /**
     * @return string
     */
    public function primaryTableName();
}
