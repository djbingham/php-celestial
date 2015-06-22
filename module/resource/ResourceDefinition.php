<?php
namespace Sloth\Module\Resource;

use Sloth\Exception\InvalidArgumentException;

class ResourceDefinition implements Base\ResourceDefinition
{
    /**
     * @var array
     */
    private $manifest;

    /**
     * @var Definition\AttributeList
     */
    private $attributeList;

    /**
     * @var Definition\TableList
     */
    private $tableList;

    /**
     * @var array
     */
    private $tableSelectOrder = array();

    /**
     * @var array
     */
    private $views = array();

    public function __construct(array $manifest)
    {
        $this->manifest = $manifest;
        $this->attributeList = new Definition\AttributeList($manifest['attributes']);
        $this->tableList = new Definition\TableList($manifest['tables']);
        $this->tableSelectOrder = $manifest['tableSelectOrder'];
        foreach ($manifest['views'] as $viewName => $viewPath) {
            $this->views[$viewName] = $viewPath;
        }
    }

    public function name()
    {
        return $this->getManifestProperty('name');
    }

    public function attributeList()
    {
        return $this->attributeList;
    }

    public function autoAttribute()
    {
        return $this->getManifestProperty('autoAttribute');
    }

    public function primaryAttribute()
    {
        return $this->getManifestProperty('primaryAttribute');
    }

    public function resourceClass()
    {
        return $this->getManifestProperty('resourceClass');
    }

    public function factoryClass()
    {
        return $this->getManifestProperty('factoryClass');
    }

    public function views()
    {
        return $this->views;
    }

    public function view($name)
    {
        $views = $this->views();
        if (!array_key_exists($name, $views)) {
            throw new InvalidArgumentException(
                sprintf('Unrecognised view requested from resource definition: %s', $name)
            );
        }
        return $views[$name];
    }

    public function tableList()
    {
        return $this->tableList;
    }

    public function table($name)
    {
        return $this->tableList->getByName($name);
    }

    public function tableSelectOrder()
    {
        return $this->tableSelectOrder;
    }

    public function primaryTable()
    {
        return $this->tableList->getPrimaryTable();
    }

    /**
     * @param string $propertyName
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function getManifestProperty($propertyName)
    {
        if (!array_key_exists($propertyName, $this->manifest)) {
            throw new InvalidArgumentException(
                sprintf('Unrecognised property requested from manifest: %s', $propertyName)
            );
        }
        return $this->manifest[$propertyName];
    }
}
