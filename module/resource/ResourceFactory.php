<?php
namespace Sloth\Module\Resource;

class ResourceFactory implements Base\ResourceFactory
{
    /**
     * @var Base\ResourceDefinition
     */
    protected $definition;

    /**
     * @var QueryFactory
     */
	protected $queryFactory;

	public function __construct(Base\ResourceDefinition $definition, QueryFactory $queryFactory)
	{
		$this->definition = $definition;
		$this->queryFactory = $queryFactory;
	}

	public function getDefinition()
	{
		return $this->definition;
	}

    public function createResource(array $attributes)
    {
        $resource = new Resource($this);
        $resource->setAttributes($attributes);
        return $resource;
    }

	public function getBy(array $attributes)
	{
        $database = $this->queryFactory->getDatabase();

        $query = $this->queryFactory->selectByAttributes($this->definition, $attributes);
        $database->execute($query);

        return $this->createResourceList($database->getData());
	}

	public function search(array $filters)
	{
        $database = $this->queryFactory->getDatabase();

        $query = $this->queryFactory->search($this->definition, $filters);
        $database->execute($query);

		return $this->createResourceList($database->getData());
	}

	public function create(array $attributes)
	{
        $database = $this->queryFactory->getDatabase();

        $attributes = $this->encodeAttributes($attributes);
        $query = $this->queryFactory->insertSingle($this->definition, $attributes);
        $database->execute($query);

        $attributes = $this->decodeAttributes($attributes);
        $attributes[$this->definition->autoAttribute()] = $database->getInsertId();
        return $this->createResource($attributes);
	}

	public function update(Base\Resource $resource)
	{
        $database = $this->queryFactory->getDatabase();
        $attributes = $this->encodeAttributes($resource->getAttributes());
        $query = $this->queryFactory->updateById($this->definition, $attributes);
        $database->execute($query);
        return $resource;
	}

	public function delete(Base\Resource $resource)
	{
        $database = $this->queryFactory->getDatabase();
        $attributes = $this->encodeAttributes($resource->getAttributes());
        $query = $this->queryFactory->deleteByAttributes($this->definition, $attributes);
        $database->execute($query);
        return $resource;
	}

    protected function createResourceList(array $data)
    {
        $resourceList = new ResourceList($this);
        foreach ($data as $row) {
            $row = $this->decodeAttributes($row);
            $resourceList->push($this->createResource($row));
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
            $attributes[$name] = utf8_decode($value);
        }
        return $attributes;
    }
}
