<?php
namespace Sloth\Module\Graph;

class ResourceFactory implements ResourceFactoryInterface
{
	/**
	 * @var Definition\Table
	 */
	protected $tableDefinition;

	/**
	 * @var QuerySetFactory
	 */
	protected $querySetFactory;

	public function __construct(Definition\Table $definition, QuerySetFactory $querySetFactory)
	{
		$this->tableDefinition = $definition;
		$this->querySetFactory = $querySetFactory;
	}

	public function getTableDefinition()
	{
		return $this->tableDefinition;
	}

	public function getBy(array $attributesToInclude, array $filters)
	{
		$tableDefinition = $this->filterResourceAttributes($this->tableDefinition, $attributesToInclude);
		$data = $this->querySetFactory->getBy()->execute($tableDefinition, $filters);
        return $this->instantiateResourceList($data);
	}

	public function search(array $attributesToInclude, array $filters)
	{
		$tableDefinition = $this->filterResourceAttributes($this->tableDefinition, $attributesToInclude);
		$data = $this->querySetFactory->search()->execute($tableDefinition, $filters);
		return $this->instantiateResourceList($data);
	}

	public function create(array $attributes)
	{
		$attributes = $this->encodeAttributes($attributes);
		$data = $this->querySetFactory->insert()->execute($this->tableDefinition, array(), $attributes);
		return $this->instantiateResource($data);
	}

	public function update(array $filters, array $attributes)
	{
		$attributes = $this->encodeAttributes($attributes);
		$data = $this->querySetFactory->update()->execute($this->tableDefinition, $filters, $attributes);
		return $this->instantiateResource($data);
	}

	public function delete(array $filters)
	{
		$data = $this->querySetFactory->delete()->execute($this->tableDefinition, $filters);
		return $this->instantiateResource($data);
	}

	public function instantiateResource(array $attributes)
	{
		$resource = new Resource($this);
		$resource->setAttributes($attributes);
		return $resource;
	}

	public function instantiateResourceList(array $data)
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
			if (is_array($value)) {
				$attributes[$name] = $this->encodeAttributes($value);
			} else {
				$attributes[$name] = utf8_encode($value);
			}
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

    private function filterResourceAttributes(Definition\Table $tableDefinition, array $attributeMap)
    {
        foreach ($tableDefinition->fields as $attributeIndex => $attribute) {
            if (!array_key_exists($attribute->name, $attributeMap)) {
                $tableDefinition->fields->removeByIndex($attributeIndex);
            }
        }
		for ($linkIndex = 0; $linkIndex < $tableDefinition->links->length(); $linkIndex++) {
			$link = $tableDefinition->links->getByIndex($linkIndex);
            /** @var \Sloth\Module\Graph\Definition\Table\Join $link */
            if (array_key_exists($link->name, $attributeMap)) {
				$this->filterResourceAttributes($link->getChildTable(), $attributeMap[$link->name]);
			} else {
				$tableDefinition->links->removeByIndex($linkIndex);
				$linkIndex--;
            }
        }
        return $tableDefinition;
    }
}
