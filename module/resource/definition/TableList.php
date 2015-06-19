<?php
namespace Sloth\Module\Resource\Definition;

use Sloth\Exception\InvalidArgumentException;

class TableList
{
    /**
     * @var array
     */
	private $tables = array();

    public function __construct(array $tables)
    {
        foreach ($tables as $tableIndex => $tableManifest) {
            if (array_key_exists('links', $tableManifest)) {
                foreach ($tableManifest['links'] as $parentTable => $linksToParent) {
                    $links = array();
                    foreach ($linksToParent as $parentTableField => $childTableField) {
                        list($parentTable, $parentField) = explode('.', $parentTableField);
                        list($childTable, $childField) = explode('.', $childTableField);
                        $links[] = new TableLink(array(
                            'parentTable' => $parentTable,
                            'parentField' => $parentField,
                            'childTable' => $childTable,
                            'childField' => $childField
                        ));
                    }
                    $tables[$tableIndex]['links'][$parentTable] = $links;
                }
            }
            $this->tables[$tableIndex] = new Table($tableManifest);
        }
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->tables;
    }

    /**
     * @return array
     */
    public function getTableNames()
    {
        return array_keys($this->tables);
    }

    /**
     * @param string $name
     * @return Table
     * @throws InvalidArgumentException
     */
    public function getByName($name)
    {
        if (!array_key_exists($name, $this->tables)) {
            throw new InvalidArgumentException(
                sprintf('Unrecognised table requested from resource definition: %s', $name)
            );
        }
        return $this->tables[$name];
    }
}
