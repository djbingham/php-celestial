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
     * @var string
     */
    private $primaryTableName;

    public function __construct(array $manifest)
    {
        $this->manifest = $manifest;
    }

    public function name()
    {
        return $this->getManifestProperty('name');
    }

    public function attributes()
    {
        return $this->getManifestProperty('attributes');
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
        return $this->getManifestProperty('views');
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

    public function tables()
    {
        return $this->getManifestProperty('tables');
    }

    public function tableNames()
    {
        if (!isset($this->modelNames)) {
            $this->modelNames = array_keys($this->tables());
        }
        return $this->modelNames;
    }

    public function table($name)
    {
        $tables = $this->tables();
        if (!array_key_exists($name, $tables)) {
            throw new InvalidArgumentException(
                sprintf('Unrecognised model requested from resource definition: %s', $name)
            );
        }
        return $tables[$name];
    }

    public function primaryTableName()
    {
        if (!isset($this->primaryTableName)) {
            foreach ($this->getManifestProperty('tables') as $table){
                if ($table['type'] === 'primary') {
                    $this->primaryTableName = $table['name'];
                }
            }
        }
        return $this->primaryTableName;
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
