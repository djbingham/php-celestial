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
     * @var Definition\Table
     */
    private $primaryTable;

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
    private $views = array();

    public function __construct(array $manifest)
    {
        $this->manifest = $manifest;
        $this->attributeList = new Definition\AttributeList($manifest['attributes']);
        $this->tableList = new Definition\TableList($manifest['tables']);
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

    public function primaryTable()
    {
        if (!isset($this->primaryTable)) {
            foreach ($this->tableList->getAll() as $table) {
                if ($this->tableIsPrimary($table)) {
                    $this->primaryTable = $table;
                }
            }
        }
        return $this->primaryTable;
    }

    private function tableIsPrimary(Definition\Table $table)
    {
        return $table->isPrimary();
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
