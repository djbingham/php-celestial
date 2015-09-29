<?php
namespace Sloth\Module\Resource;

use Sloth\Module\Resource\Definition\Table\Field;
use Sloth\Module\Resource\Definition\Table\Join;

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
//		$tableJoins = $tableDefinition->links;
//		/** @var Join $join */
//		foreach ($tableJoins as $join) {
//			if (array_key_exists($join->name, $attributeMap)) {
//				/** @var Join\Constraint $constraint */
//				foreach ($join->getConstraints() as $constraint) {
//					$attributeMap[$join->name][$constraint->childField->name] = true;
//				}
//			}
//		}

		/** @var Field $field */
		foreach ($tableDefinition->fields as $attributeIndex => $field) {
			if (!array_key_exists($field->name, $attributeMap)) {
				$tableDefinition->fields->removeByIndex($attributeIndex);
            }
        }

		for ($joinIndex = 0; $joinIndex < $tableDefinition->links->length(); $joinIndex++) {
			/** @var \Sloth\Module\Resource\Definition\Table\Join $join */
			$join = $tableDefinition->links->getByIndex($joinIndex);
            if (array_key_exists($join->name, $attributeMap)) {
				$this->filterResourceAttributes($join->getChildTable(), $attributeMap[$join->name]);
			} else {
				$tableDefinition->links->removeByIndex($joinIndex);
				$joinIndex--;
            }
        }
        return $tableDefinition;
    }
}
