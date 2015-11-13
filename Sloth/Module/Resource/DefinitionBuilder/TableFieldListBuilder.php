<?php
namespace Sloth\Module\Resource\DefinitionBuilder;

use Sloth\Module\Resource\Definition;

class TableFieldListBuilder
{
    /**
     * @var TableFieldBuilder
     */
    private $fieldBuilder;

    public function __construct(TableFieldBuilder $fieldBuilder)
    {
        $this->fieldBuilder = $fieldBuilder;
    }

    public function build(Definition\Table $table, \stdClass $fieldsManifest)
    {
        $fields = new Definition\Table\FieldList();
        foreach ($fieldsManifest as $fieldName => $fieldManifest) {
            $fieldManifest->name = $fieldName;
            $fields->push($this->fieldBuilder->build($table, $fieldManifest));
        }
        return $fields;
    }
}
