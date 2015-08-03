<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Helper\ObjectList;

class AttributeList extends ObjectList
{
	/**
	 * @var string
	 */
	protected $alias;

	public function push(Attribute $attribute)
	{
		$this->items[] = $attribute;
		return $this;
	}

	public function setAlias($alias)
	{
		$this->alias = $alias;
		return $this;
	}

	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * @param string $index
	 * @return Attribute
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	/**
	 * @param string $name
	 * @return Attribute
	 */
	public function getByName($name)
	{
		return $this->getByProperty('name', $name);
	}

	/**
	 * @param string $alias
	 * @return Attribute
	 */
	public function getByFieldAlias($alias)
	{
		$foundAttribute = null;
		foreach ($this as $attribute) {
			/** @var Attribute $attribute */
			if ($attribute->field->alias === $alias) {
				$foundAttribute = $attribute;
				break;
			}
		}
		return $foundAttribute;
	}

	/**
	 * @param string $resourceAlias
	 * @return AttributeList
	 */
	public function getByResourceAlias($resourceAlias)
	{
		$matchedAttributes = new self();
		foreach ($this as $attribute) {
			/** @var Attribute $attribute */
			if ($attribute->resource->getAlias() === $resourceAlias) {
				$matchedAttributes->push($attribute);
			}
		}
		return $matchedAttributes;
	}

	public function remove(Attribute $attribute)
	{
		$index = $this->indexOf($attribute);
		if ($index !== -1) {
			unset($this->items[$index]);
		}
		return $this;
	}

	public function indexOf(Attribute $attribute)
	{
		$foundIndex = -1;
		foreach ($this->items as $index => $item) {
			/** @var Attribute $item */
			if ($item->name === $attribute->name) {
				$foundIndex = $index;
				break;
			}
		}
		return $foundIndex;
	}

	public function indexOfName($attributeName)
	{
		$foundIndex = -1;
		foreach ($this->items as $index => $item) {
			/** @var Attribute $item */
			if ($item->name === $attributeName) {
				$foundIndex = $index;
				break;
			}
		}
		return $foundIndex;
	}

	public function getFields()
	{
		$fields = new TableFieldList();
		foreach ($this as $attribute) {
			/** @var Attribute $attribute */
			$fields->push($attribute->field);
		}
		return $fields;
	}
}
