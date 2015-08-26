<?php
namespace Sloth\Module\Resource\Definition;

use Sloth\Exception\InvalidArgumentException;

class TableList
{
    /**
     * @var array
     */
	private $tables = array();

    /**
     * @var Table
     */
    private $primaryTable;

    public function __construct(array $tables)
    {
        foreach ($tables as $tableIndex => $tableManifest) {
            if ($tableManifest instanceof Table) {
                $table = $tableManifest;
            } else {
                $table = new Table($tableManifest);
            }
            $this->tables[$tableIndex] = $table;
        }
    }

    public function append($alias, Table $table)
    {
        $this->tables[$alias] = $table;
        return $this;
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
     * @return bool
     */
    public function contains($name)
    {
        return array_key_exists($name, $this->tables);
    }

    public function isJoinedByLink(TableLink $link)
    {
        return $this->contains($link->getParentTable()) && $this->contains($link->getChildTable());
    }

    public function getLinksToOtherTables()
    {
        $links = array();
        foreach ($this->tables as $table) {
            $links = array_merge($links, $this->getLinksFromTableToOutOfList($table));
        }
        return $links;
    }

    private function getLinksFromTableToOutOfList(Table $table)
    {
        $links = array();
        foreach ($table->getLinksToParents() as $parent => $linksToParent) {
            $links[$parent] = array();
            foreach ($linksToParent as $link) {
                if (!$this->isJoinedByLink($link)) {
                    $links[$parent][$link->getParentField()] = $link;
                }
            }
        }
        return $links;
    }

    /**
     * @param string $name
     * @return Table
     * @throws InvalidArgumentException
     */
    public function getByName($name)
    {
        if (!$this->contains($name)) {
            throw new InvalidArgumentException(
                sprintf('Unrecognised table requested from list: %s', $name)
            );
        }
        return $this->tables[$name];
    }

    public function getPrimaryTable()
    {
        if (!isset($this->primaryTable)) {
            foreach ($this->tables as $table) {
                if ($this->tableIsPrimary($table)) {
                    $this->primaryTable = $table;
                }
            }
            if (!isset($this->primaryTable)) {
                $firstTableName = array_keys($this->tables)[0];
                $this->primaryTable = $this->getByName($firstTableName);
            }
        }
        return $this->primaryTable;
    }

    public function getAttributeList()
    {
        $attributes = array();
        foreach ($this->getAll() as $table) {
            $attributes = array_merge($attributes, $this->getAttributesFromTable($table));
        }
        return new AttributeList($attributes);
    }

    public function getFetchOrder()
    {
        $orders = array();
        foreach ($this->getAll() as $table) {
            $orders = array_merge($orders, $this->getFetchOrderFromTable($table));
        }
        return $orders;
    }

    private function getFetchOrderFromTable(Table $table)
    {
        return $table->getFetchOrder();
    }

    private function getAttributesFromTable(Table $table)
    {
        return $table->getAttributeList()->getAll();
    }

    private function tableIsPrimary(Table $table)
    {
        return $table->isPrimary();
    }
}
