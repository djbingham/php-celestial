<?php
namespace Sloth\Module\Resource\Definition;

class Table
{
    /**
     * @var string
     */
	private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $autoIncrement;

    /**
     * @var string
     */
    private $primaryKey;

    /**
     * @var AttributeList
     */
    private $attributes;

    /**
     * @var array
     */
    private $fetchOrder = array();

    /**
     * @var array
     */
    private $links = array();

    /**
     * @var Boolean
     */
    private $primary;

    /**
     * @var Boolean
     */
    private $editable;

    public function __construct(array $properties)
    {
//        if (array_key_exists('attributes', $properties)) {
//            $attributes = array();
//            foreach ($properties['attributes'] as $attributeName => $fieldName) {
//                $attributes[$attributeName] = new Attribute(array(
//                    'name' => $attributeName,
//                    'tableName' => $properties['name'],
//                    'fieldName' => $fieldName
//                ));
//            }
//        } else {
//            $attributes = array();
//        }
//        $properties['attributes'] = new AttributeList($attributes);

        if (array_key_exists('links', $properties)) {
            foreach ($properties['links'] as $parentTable => $linksToParent) {
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
                $properties['links'][$parentTable] = $links;
            }
        }

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

    public function getType()
    {
        return $this->type;
    }

    public function getAutoIncrement()
    {
        return $this->autoIncrement;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getAttributeList()
    {
        return $this->attributes;
    }

    public function getFetchOrder()
    {
        return $this->fetchOrder;
    }

    public function getLinksToParents(TableList $possibleParents = null)
    {
        if (!is_null($possibleParents)) {
            $links = array();
            foreach ($this->links as $parent => $linksToParent) {
                foreach ($linksToParent as $link) {
                    if ($possibleParents->isJoinedByLink($link)) {
                        $links[$parent][] = $link;
                    }
                }
            }
        } else {
            $links = $this->links;
        }
        return $links;
    }

    public function isPrimary()
    {
        return $this->primary;
    }

    public function isEditable()
    {
        return $this->editable;
    }

}
