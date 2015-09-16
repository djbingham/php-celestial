<?php
namespace Sloth\Module\Graph\DefinitionBuilder;

use Sloth\Module\Graph\Definition;

class TableFieldBuilder
{
	/**
	 * @var ValidatorListBuilder
	 */
	private $validatorListBuilder;

	/**
	 * @var array
	 */
	private $cache = array();

	public function __construct(ValidatorListBuilder $validatorListBuilder)
	{
		$this->validatorListBuilder = $validatorListBuilder;
	}

	public function build(Definition\Table $table, array $fieldManifest)
	{
		$field = $this->getCachedField($table->alias, $fieldManifest['name']);
		if (is_null($field)) {
			$field = new Definition\Table\Field();
			$field->table = $table;
			$field->name = $fieldManifest['name'];
			$field->alias = sprintf('%s.%s', $table->getAlias(), $fieldManifest['field']);
			$field->autoIncrement = array_key_exists('autoIncrement', $fieldManifest) ? $fieldManifest['autoIncrement'] : false;
			$field->type = $fieldManifest['type'];
			$validatorManifest = array_key_exists('validators', $fieldManifest) ? $fieldManifest['validators'] : array();
			$field->validators = $this->validatorListBuilder->build($validatorManifest);
			$this->cacheField($field);
		}
		return $field;
	}

	private function cacheField(Definition\Table\Field $field)
	{
		$tableName = $field->table->name;
		$this->cache[$tableName][$field->name] = $field;
		return $this;
	}

	/**
	 * @param string $tableName
	 * @param string $fieldName
	 * @return \Sloth\Module\Graph\Definition\Table\Field
	 */
	private function getCachedField($tableName, $fieldName)
	{
		if (!array_key_exists($tableName, $this->cache)) {
			return null;
		}
		if (!array_key_exists($fieldName, $this->cache[$tableName])) {
			return null;
		}
		return $this->cache[$tableName][$fieldName];
	}
}
