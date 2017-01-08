<?php
namespace Sloth\Module\Data\Resource;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Data\Resource\Face\ResourceFactoryInterface;
use Sloth\Module\Data\Resource\Face\ResourceInterface;
use Sloth\Module\Data\Table\Face\FieldInterface;
use Sloth\Module\Data\Table\Face\JoinInterface;
use Sloth\Module\Data\Table\Face\TableInterface;

class Resource implements ResourceInterface
{
	private $factory;
	private $attributes = array();

	public function __construct(ResourceFactoryInterface $factory)
	{
		$this->factory = $factory;
	}

	public function getDefinition()
	{
		return $this->factory->getResourceDefinition();
	}

	public function save()
	{
		$this->factory->update($this);
	}

	public function delete()
	{
		$filters = $this->getAttributes();
		$filters = $this->reduceFiltersToPrimaryKeys($filters, $this->getDefinition()->table);

		$this->factory->delete($filters);
	}

	public function setAttributes(array $attributes)
	{
		foreach ($attributes as $name => $value) {
			$this->setAttribute($name, $value);
		}
		return $this;
	}

	public function getAttributes()
	{
		return $this->attributes;
	}

	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
		return $this;
	}

	public function &getAttribute($name)
	{
		if (!$this->hasAttribute($name)) {
			$attributeList = json_encode(array_keys($this->getAttributes()));
			throw new InvalidArgumentException(
				sprintf('Attribute `%s` not found in resource with attributes: %s', $name, $attributeList)
			);
		}
		return $this->attributes[$name];
	}

	public function hasAttribute($name)
	{
		return array_key_exists($name, $this->attributes);
	}

	private function reduceFiltersToPrimaryKeys(array $filters, TableInterface $tableDefinition)
	{
		$reducedFilters = array();

		/** @var FieldInterface $field */
		foreach ($tableDefinition->fields as $field) {
			if (array_key_exists($field->name, $filters)) {
				$reducedFilters[$field->name] = $filters[$field->name];

				if ($field->isUnique === true) {
					// Ensure that we only include a single field if there is a unique field, to improve efficiency
					$reducedFilters = array(
						$field->name => $reducedFilters[$field->name]
					);
					break;
				}
			}
		}

		/** @var JoinInterface $join */
		foreach ($tableDefinition->links as $join) {
			if (array_key_exists($join->name, $filters)) {
				$childTable = $join->getChildTable();
				$subFilters = $this->reduceFiltersToPrimaryKeys($filters[$join->name], $childTable);
				if (!empty($subFilters)) {
					$reducedFilters[$join->name] = $subFilters;
				}
			}
		}

		return $reducedFilters;
	}
}
