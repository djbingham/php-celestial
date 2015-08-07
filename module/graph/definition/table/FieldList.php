<?php
namespace Sloth\Module\Graph\Definition\Table;

use Sloth\Module\Graph\Definition\Table\Field;
use Sloth\Module\Graph\Helper\ObjectList;

class FieldList extends ObjectList
{
	/**
	 * @var string
	 */
	protected $alias;

	public function push(Field $attribute)
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
	 * @return Field
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	/**
	 * @param string $name
	 * @return Field
	 */
	public function getByName($name)
	{
		return $this->getByProperty('name', $name);
	}

	/**
	 * @param string $tableAlias
	 * @return FieldList
	 */
	public function getByTableAlias($tableAlias)
	{
		$matchedAttributes = new self();
		foreach ($this as $attribute) {
			/** @var Field $attribute */
			if ($attribute->table->getAlias() === $tableAlias) {
				$matchedAttributes->push($attribute);
			}
		}
		return $matchedAttributes;
	}

	public function remove(Field $attribute)
	{
		$index = $this->indexOf($attribute);
		if ($index !== -1) {
			unset($this->items[$index]);
		}
		return $this;
	}

	public function indexOf(Field $attribute)
	{
		$foundIndex = -1;
		foreach ($this->items as $index => $item) {
			/** @var Field $item */
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
			/** @var Field $item */
			if ($item->name === $attributeName) {
				$foundIndex = $index;
				break;
			}
		}
		return $foundIndex;
	}
}
