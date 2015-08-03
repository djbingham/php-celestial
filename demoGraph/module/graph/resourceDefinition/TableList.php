<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Helper\ObjectList;
use Sloth\Exception\InvalidArgumentException;

class TableList extends ObjectList
{
    /**
     * @var Table
     */
    private $primaryTable;

    public function setPrimaryTable(Table $table)
    {
        $this->primaryTable = $table;
        return $this;
    }

    public function getPrimaryTable()
    {
        return $this->primaryTable;
    }

    public function push(Table $table)
    {
        $this->items[] = $table;
        return $this;
    }

    /**
     * @return Table
     */
    public function shift()
    {
        $item = array_shift($this->items);
        return $item;
    }

    /**
     * @param string $index
     * @return Table
     */
    public function getByIndex($index)
    {
        return parent::getByIndex($index);
    }

    /**
     * @param string $name
     * @return Table
     */
    public function getByName($name)
    {
        return parent::getByProperty('name', $name);
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function containsAlias($alias)
    {
        try {
            $foundTable = $this->getByProperty('alias', $alias);
        } catch (InvalidArgumentException $e) {
            try {
                $foundTable = $this->getByProperty('name', $alias);
            } catch (InvalidArgumentException $e) {
                $foundTable = false;
            }
        }
        return ($foundTable instanceof Table);
    }
}
