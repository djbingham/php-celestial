<?php
namespace Sloth\Module\Data\Resource;

use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Data\ResourceDataValidator\ResourceDataValidatorModule;
use Sloth\Module\Data\Table\Face\FieldInterface;
use Sloth\Module\Data\Table\Face\JoinInterface;
use Sloth\Module\Data\Table\Face\TableInterface;
use Sloth\Module\Data\TableQuery\TableQueryModule;
use Sloth\Module\Data\Resource\Definition\Resource\AttributeList;
use Sloth\Module\Data\Resource\Face\ResourceFactoryInterface;
use Sloth\Module\Data\Resource\Face\Definition\ResourceInterface;

class ResourceFactory implements ResourceFactoryInterface
{
	/**
	 * @var ResourceInterface
	 */
	protected $resourceDefinition;

	/**
	 * @var TableQueryModule
	 */
	protected $tableQueryModule;

	/**
	 * @var ResourceDataValidatorModule
	 */
	private $dataValidator;

	public function __construct(
		ResourceInterface $definition,
		TableQueryModule $tableQueryModule,
		ResourceDataValidatorModule $dataValidator
	) {
		$this->resourceDefinition = $definition;
		$this->tableQueryModule = $tableQueryModule;
		$this->dataValidator = $dataValidator;
	}

	public function getResourceDefinition()
	{
		return $this->resourceDefinition;
	}

	public function getBy(AttributeList $attributesToInclude, array $filters)
	{
		$tableDefinition = $this->filterTableFields($this->resourceDefinition->table, $attributesToInclude);
		$data = $this->tableQueryModule->getBy()->execute($tableDefinition, $filters);
        return $this->instantiateResourceList($data);
	}

	public function search(AttributeList $attributesToInclude, array $filters)
	{
		$tableDefinition = $this->filterTableFields($this->resourceDefinition->table, $attributesToInclude);
		$data = $this->tableQueryModule->search()->execute($tableDefinition, $filters);
		return $this->instantiateResourceList($data);
	}

	public function create(array $attributes)
	{
		$attributes = $this->encodeAttributes($attributes);
		if ($this->dataValidator->validateInsertData($this->resourceDefinition, $attributes)) {
			$data = $this->tableQueryModule->insert()->execute($this->resourceDefinition->table, array(), $attributes);
			$resource = $this->instantiateResource($data[0]);
		} else {
			throw new InvalidRequestException('Invalid attribute values given to create resource.');
		}

		return $resource;
	}

	public function update(array $filters, array $attributes)
	{
		$attributes = $this->encodeAttributes($attributes);
		if ($this->dataValidator->validateUpdateData($this->resourceDefinition, $attributes)) {
			$data = $this->tableQueryModule->update()->execute($this->resourceDefinition->table, $filters, $attributes);
			$resource = $this->instantiateResource($data);
		} else {
			throw new InvalidRequestException('Invalid attribute values given to update resource.');
		}

		return $resource;
	}

	public function delete(array $filters)
	{
		$data = $this->tableQueryModule->delete()->execute($this->resourceDefinition->table, $filters);
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

    private function filterTableFields(TableInterface $tableDefinition, AttributeList $attributes)
    {
		if (!empty($attributes)) {
			/** @var FieldInterface $field */
			foreach ($tableDefinition->fields as $attributeIndex => $field) {
				if ($attributes->indexOfPropertyValue('name', $field->name) === -1) {
					$tableDefinition->fields->removeByIndex($attributeIndex);
				}
			}

			for ($joinIndex = 0; $joinIndex < $tableDefinition->links->length(); $joinIndex++) {
				/** @var JoinInterface $join */
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
