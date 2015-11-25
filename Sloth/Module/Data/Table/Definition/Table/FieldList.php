<?php
namespace Sloth\Module\Data\Table\Definition\Table;

use Sloth\Helper\ObjectList;
use Sloth\Module\Data\Table\Face\FieldListInterface;

class FieldList extends ObjectList implements FieldListInterface
{
	/**
	 * @var string
	 */
	protected $alias;

	public function push(Field $field)
	{
		$this->append($field);
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
	 * @param string $name
	 * @return Field
	 */
	public function getByAlias($name)
	{
		return $this->getByProperty('alias', $name);
	}

	/**
	 * @param string $tableAlias
	 * @return FieldList
	 */
	public function getByTableAlias($tableAlias)
	{
		$matchedFields = new self();
		foreach ($this as $field) {
			/** @var Field $field */
			if ($field->table->getAlias() === $tableAlias) {
				$matchedFields->push($field);
			}
		}
		return $matchedFields;
	}

	public function remove(Field $field)
	{
		$index = $this->indexOf($field);
		if ($index !== -1) {
			unset($this->items[$index]);
		}
		return $this;
	}

	public function indexOf(Field $field)
	{
		$foundIndex = -1;
		foreach ($this->items as $index => $item) {
			/** @var Field $item */
			if ($item->name === $field->name) {
				$foundIndex = $index;
				break;
			}
		}
		return $foundIndex;
	}

	public function indexOfName($fieldName)
	{
		$foundIndex = -1;
		foreach ($this->items as $index => $item) {
			/** @var Field $item */
			if ($item->name === $fieldName) {
				$foundIndex = $index;
				break;
			}
		}
		return $foundIndex;
	}

	public function indexOfAlias($fieldAlias)
	{
		$foundIndex = -1;
		foreach ($this->items as $index => $item) {
			/** @var Field $item */
			if ($item->alias === $fieldAlias) {
				$foundIndex = $index;
				break;
			}
		}
		return $foundIndex;
	}
}
