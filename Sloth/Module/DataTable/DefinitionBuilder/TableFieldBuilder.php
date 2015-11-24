<?php
namespace Sloth\Module\DataTable\DefinitionBuilder;

use Sloth\Module\DataTable\Definition;

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

	public function build(Definition\Table $table, \stdClass $fieldManifest)
	{
		$field = $this->getCachedField($table->alias, $fieldManifest->name);
		if (is_null($field)) {
			$field = new Definition\Table\Field();
			$field->table = $table;
			$field->name = $fieldManifest->name;
			$field->alias = sprintf('%s.%s', $table->getAlias(), $fieldManifest->field);
			$field->autoIncrement = property_exists($fieldManifest, 'autoIncrement') ? $fieldManifest->autoIncrement : false;
			$field->type = $fieldManifest->type;
			$field->validators = $this->buildFieldValidators($fieldManifest);
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
	 * @return \Sloth\Module\DataTable\Definition\Table\Field
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

	private function buildFieldValidators(\stdClass $fieldManifest)
	{
		$fieldName = $fieldManifest->name;
		$validatorListManifest = property_exists($fieldManifest, 'validators') ? $fieldManifest->validators : array();

		$formattedManifest = array();

		foreach ($validatorListManifest as $validatorType => $validatorValue) {
			$formattedManifest[] = $this->buildValidatorManifest($fieldName, $validatorType, $validatorValue);
		}

		return $this->validatorListBuilder->build($formattedManifest);
	}

	private function buildValidatorManifest($fieldName, $validatorType, $validatorValue)
	{
		return (object)array(
			'fields' => array($fieldName),
			'rule' => $validatorType,
			'options' => array(
				'compareTo' => $validatorValue
			)
		);
	}
}
