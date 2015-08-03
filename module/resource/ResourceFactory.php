<?php
namespace Sloth\Module\Resource;

class ResourceFactory implements Base\ResourceFactory
{
    /**
     * @var Base\ResourceDefinition
     */
    protected $definition;

    /**
     * @var QuerySetFactory
     */
	protected $querySetFactory;

	public function __construct(Base\ResourceDefinition $definition, QuerySetFactory $querySetFactory)
	{
		$this->definition = $definition;
		$this->querySetFactory = $querySetFactory;
	}

	public function getDefinition()
	{
		return $this->definition;
	}

    public function instantiateResource(array $attributes)
    {
        $resource = new Resource($this);
        $resource->setAttributes($attributes);
        return $resource;
    }

	public function getBy(array $attributes)
	{
        $querySet = $this->querySetFactory->getBy();
        $querySet->setResourceDefinition($this->definition)
            ->setAttributeValues($attributes);
        return $this->instantiateResourceList($querySet->execute());
	}

	public function search(array $filters)
	{
        $querySet = $this->querySetFactory->search();
        $querySet->setResourceDefinition($this->definition)
            ->setFilters($filters);
        return $this->instantiateResourceList($querySet->execute());
	}

	public function create(array $attributes)
	{
        $querySet = $this->querySetFactory->insertRecord();
        $querySet->setResourceDefinition($this->definition)
            ->setAttributeValues($attributes);
        return $this->instantiateResource($querySet->execute());
	}

	public function update(Base\Resource $resource)
	{
        $database = $this->querySetFactory->getDatabase();
        $attributes = $this->encodeAttributes($resource->getAttributes());
        $query = $this->querySetFactory->updateById($this->definition, $attributes);
        $database->execute($query);
        return $resource;
	}

	public function delete(Base\Resource $resource)
	{
        $database = $this->querySetFactory->getDatabase();
        $attributes = $this->encodeAttributes($resource->getAttributes());
        $query = $this->querySetFactory->deleteByAttributes($this->definition, $attributes);
        $database->execute($query);
        return $resource;
	}

    protected function instantiateResourceList(array $data)
    {
        $resourceList = new ResourceList($this);
        foreach ($data as $row) {
            $row = $this->decodeAttributes($row);
            $resourceList->push($this->instantiateResource($row));
        }
        return $resourceList;
    }

    protected function encodeAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $attributes[$name] = utf8_encode($value);
        }
        return $attributes;
    }

    protected function decodeAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            if (is_array($value)) {
                $attributes[$name] = $this->decodeAttributes($value);
            } else {
                $attributes[$name] = utf8_decode($value);
            }
        }
        return $attributes;
    }
}
