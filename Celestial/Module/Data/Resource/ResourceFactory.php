<?php
namespace Celestial\Module\Data\Resource;

use Celestial\App;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Data\ResourceDataValidator\ResourceDataValidatorModule;
use Celestial\Module\Data\Table\Definition\Table;
use Celestial\Module\Data\Table\Face\FieldInterface;
use Celestial\Module\Data\Table\Face\JoinInterface;
use Celestial\Module\Data\Table\Face\TableInterface;
use Celestial\Module\Data\TableQuery\TableQueryModule;
use Celestial\Module\Data\Resource\Definition\Resource\AttributeList;
use Celestial\Module\Data\Resource\Face\ResourceFactoryInterface;
use Celestial\Module\Data\Resource\Face\Definition\ResourceInterface;

class ResourceFactory implements ResourceFactoryInterface
{
	/**
	 * @var App
	 */
	protected $app;

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
		App $app,
		ResourceInterface $definition,
		TableQueryModule $tableQueryModule,
		ResourceDataValidatorModule $dataValidator
	) {
		$this->app = $app;
		$this->resourceDefinition = $definition;
		$this->tableQueryModule = $tableQueryModule;
		$this->dataValidator = $dataValidator;
		$this->initialise();
	}

	public function initialise()
	{
		// Custom resource factories can overwrite this method to define instantiation behaviour
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

	public function validateCreateData(array $attributes)
	{
		return $this->dataValidator->validateInsertData($this->resourceDefinition, $attributes);
	}

	public function create(array $attributes)
	{
		$attributes = $this->encodeAttributes($attributes, $this->resourceDefinition->table);
		$validation = $this->dataValidator->validateInsertData($this->resourceDefinition, $attributes);
		if ($validation->isValid()) {
			$data = $this->tableQueryModule->insert()->execute($this->resourceDefinition->table, array(), $attributes);
			$resource = $this->instantiateResource($data[0]);
		} else {
			$errors = implode("\r\n", $validation->getErrors()->getMessages());
			throw new InvalidRequestException(
				sprintf('Invalid attribute values given to create resource. Errors: %s', $errors)
			);
		}

		return $resource;
	}

	public function validateUpdateData(array $attributes)
	{
		return $this->dataValidator->validateUpdateData($this->resourceDefinition, $attributes);
	}

	public function update(array $filters, array $attributes)
	{
		$attributes = $this->encodeAttributes($attributes, $this->resourceDefinition->table);
		$validation = $this->dataValidator->validateUpdateData($this->resourceDefinition, $attributes);

		if ($validation->isValid()) {
			$data = $this->tableQueryModule->update()->execute($this->resourceDefinition->table, $filters, $attributes);
			$resource = $this->instantiateResourceList($data);
		} else {
			$errors = implode(" \r\n", $validation->getErrors()->getMessages());
			throw new InvalidRequestException(
				sprintf('Invalid attribute values given to update resource. Errors: %s', $errors)
			);
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
			$row = $this->decodeAttributes($row, $this->resourceDefinition->table);
			$resourceList->push($this->instantiateResource($row));
		}
		return $resourceList;
	}

	protected function encodeAttributes(array $attributes, Table $tableDefinition)
	{
		foreach ($attributes as $name => $value) {
			if (is_numeric($name)) {
				$attributes[$name] = $this->encodeAttributes($value, $tableDefinition);
			} elseif (is_array($value)) {
				$childTable = $tableDefinition->links->getByName($name)->getChildTable();

				$attributes[$name] = $this->encodeAttributes($value, $childTable);
			} else {
				$field = $tableDefinition->fields->getByName($name);

				switch ($field->type) {
					case 'boolean':
						if (in_array($value, [true, 'true', '1', 1])) {
							$attributes[$name] = true;
						} elseif (in_array($value, [false, 'false', '0', 0])) {
							$attributes[$name] = false;
						}
						break;

					default:
						if (in_array($value, [null, true, false])) {
							$attributes[$name] = $value;
						} else {
							$attributes[$name] = utf8_encode($value);
						}
						break;
				}
			}
		}
		return $attributes;
	}

	protected function decodeAttributes(array $attributes, Table $tableDefinition)
	{
		foreach ($attributes as $name => $value) {
			if (is_numeric($name)) {
				$attributes[$name] = $this->decodeAttributes($value, $tableDefinition);
			} elseif (is_array($value)) {
				$childTable = $tableDefinition->links->getByName($name)->getChildTable();

				$attributes[$name] = $this->decodeAttributes($value, $childTable);
			} else {
				$field = $tableDefinition->fields->getByName($name);

				switch ($field->type) {
					case 'boolean':
						if (in_array($value, [true, 'true', '1', 1])) {
							$attributes[$name] = true;
						} elseif (in_array($value, [false, 'false', '0', 0])) {
							$attributes[$name] = false;
						}
						break;

					default:
						if (in_array($value, [null, true, false])) {
							$attributes[$name] = $value;
						} else {
							$attributes[$name] = utf8_decode($value);
						}
						break;
				}
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
