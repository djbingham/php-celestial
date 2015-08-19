<?php
namespace Sloth\Module\Graph;

class ResourceFactory implements ResourceFactoryInterface
{
	/**
	 * @var Definition\Table
	 */
	protected $resourceDefinition;

	/**
	 * @var QuerySetFactory
	 */
	protected $querySetFactory;

	public function __construct(Definition\Table $definition, QuerySetFactory $querySetFactory)
	{
		$this->resourceDefinition = $definition;
		$this->querySetFactory = $querySetFactory;
	}

	public function getResourceDefinition()
	{
		return $this->resourceDefinition;
	}

	public function instantiateResource(array $attributes)
	{
		$resource = new Resource($this);
		$resource->setAttributes($attributes);
		return $resource;
	}

	public function getBy(array $attributes, array $filters)
	{
		$resourceDefinition = $this->filterResourceAttributes($this->resourceDefinition, $attributes);
		$data = $this->querySetFactory->getBy()->execute($resourceDefinition, $filters);
        return $this->instantiateResourceList($data);
	}

	public function search(array $attributes, array $filters)
	{
		$resourceDefinition = $this->filterResourceAttributes($this->resourceDefinition, $attributes);
		$data = $this->querySetFactory->search()->execute($resourceDefinition, $filters);
		return $this->instantiateResourceList($data);
	}

	public function create(array $attributes)
	{
		$querySet = $this->querySetFactory->insertRecord();
		$querySet->setResourceDefinition($this->resourceDefinition)
			->setAttributeValues($attributes);
		return $this->instantiateResource($querySet->execute());
	}

	public function update(ResourceInterface $resource)
	{
		$database = $this->querySetFactory->getDatabase();
		$attributes = $this->encodeAttributes($resource->getAttributes());
		$query = $this->querySetFactory->updateById($this->resourceDefinition, $attributes);
		$database->execute($query);
		return $resource;
	}

	public function delete(ResourceInterface $resource)
	{
		$database = $this->querySetFactory->getDatabase();
		$attributes = $this->encodeAttributes($resource->getAttributes());
		$query = $this->querySetFactory->deleteByAttributes($this->resourceDefinition, $attributes);
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

    private function filterResourceAttributes(Definition\Table $resourceDefinition, array $attributeMap)
    {
        foreach ($resourceDefinition->fields as $attributeIndex => $attribute) {
            if (!array_key_exists($attribute->name, $attributeMap)) {
                $resourceDefinition->fields->removeByIndex($attributeIndex);
            }
        }
		for ($linkIndex = 0; $linkIndex < $resourceDefinition->links->length(); $linkIndex++) {
			$link = $resourceDefinition->links->getByIndex($linkIndex);
            /** @var \Sloth\Module\Graph\Definition\Table\Join $link */
            if (array_key_exists($link->name, $attributeMap)) {
				$this->filterResourceAttributes($link->getChildTable(), $attributeMap[$link->name]);
			} else {
				$resourceDefinition->links->removeByIndex($linkIndex);
				$linkIndex--;
            }
        }
        return $resourceDefinition;
    }
}
