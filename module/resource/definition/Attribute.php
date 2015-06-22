<?php
namespace Sloth\Module\Resource\Definition;

class Attribute
{
    /**
     * @var string
     */
	private $name;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $fieldName;

    public function __construct(array $properties)
    {
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function belongsToTableList(TableList $tableList)
    {
        $response = false;
        foreach ($tableList->getAll() as $table) {
            if ($table instanceof TableList) {
                $response = $response || $this->belongsToTableList($table);
            } else {
                $response = $response || $this->belongsToTable($table);
            }
        }
        return $response;
    }

    public function belongsToTable(Table $table)
    {
        return $this->getTableName() === $table->getName();
    }
}
