<?php
namespace Sloth\Module\Resource;

use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Definition\Table\Field;
use Sloth\Module\Resource\Definition\Table\Join;

class ResourceFactory implements ResourceFactoryInterface
{
	/**
	 * @var Definition\Resource
	 */
	protected $resourceDefinition;

	/**
	 * @var QuerySetFactory
	 */
	protected $querySetFactory;

	/**
	 * @var DataValidator
	 */
	private $dataValidator;

	public function __construct(Definition\Resource $definition, QuerySetFactory $querySetFactory, DataValidator $dataValidator)
	{
		$this->resourceDefinition = $definition;
		$this->querySetFactory = $querySetFactory;
		$this->dataValidator = $dataValidator;
	}

	public function getResourceDefinition()
	{
		return $this->resourceDefinition;
	}

	public function getBy(AttributeList $attributesToInclude, array $filters)
	{
		$tableDefinition = $this->filterTableFields($this->resourceDefinition->table, $attributesToInclude);
		$data = $this->querySetFactory->getBy()->execute($tableDefinition, $filters);
        return $this->instantiateResourceList($data);
	}

	public function search(AttributeList $attributesToInclude, array $filters)
	{
		$tableDefinition = $this->filterTableFields($this->resourceDefinition->table, $attributesToInclude);
		$data = $this->querySetFactory->search()->execute($tableDefinition, $filters);
		return $this->instantiateResourceList($data);
	}

	public function create(array $attributes)
	{
		$attributes = $this->encodeAttributes($attributes);
		if ($this->dataValidator->validate($this->resourceDefinition, $attributes)) {
			$data = $this->querySetFactory->insert()->execute($this->resourceDefinition->table, array(), $attributes);
			$resource = $this->instantiateResource($data);
		} else {
			throw new InvalidRequestException('Invalid attribute values given to create resource.');
		}

		return $resource;
	}

	public function update(array $filters, array $attributes)
	{
		$attributes = $this->encodeAttributes($attributes);
		if ($this->dataValidator->validate($this->resourceDefinition, $attributes)) {
			$data = $this->querySetFactory->update()->execute($this->resourceDefinition->table, $filters, $attributes);
			$resource = $this->instantiateResource($data);
		} else {
			throw new InvalidRequestException('Invalid attribute values given to update resource.');
		}

		return $resource;
	}

	public function delete(array $filters)
	{
		$data = $this->querySetFactory->delete()->execute($this->resourceDefinition->table, $filters);
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

    private function filterTableFields(Definition\Table $tableDefinition, AttributeList $attributes)
    {
		if (!empty($attributes)) {
			/** @var Field $field */
			foreach ($tableDefinition->fields as $attributeIndex => $field) {
				if ($attributes->indexOfPropertyValue('name', $field->name) === -1) {
					$tableDefinition->fields->removeByIndex($attributeIndex);
				}
			}

			for ($joinIndex = 0; $joinIndex < $tableDefinition->links->length(); $joinIndex++) {
				/** @var \Sloth\Module\Resource\Definition\Table\Join $join */
				$join = $tableDefinition->links->getByIndex($joinIndex);
				if ($attributes->indexOfPropertyValue('name', $join->name) === -1) {
					$tableDefinition->links->removeByIndex($joinIndex);
					$joinIndex--;
				} else {
					$this->filterTableFields($join->getChildTable(), $attributes->getByProperty('name', $join->name));
				}
			}
		}
        return $tableDefinition;
    }
}
