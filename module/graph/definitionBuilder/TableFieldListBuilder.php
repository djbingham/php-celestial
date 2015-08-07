<?php
namespace Sloth\Module\Graph\DefinitionBuilder;

use Sloth\Module\Graph\Definition;

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

    public function build(Definition\Table $table, array $fieldsManifest)
    {
        $fields = new Definition\Table\FieldList();
        foreach ($fieldsManifest as $fieldName => $fieldManifest) {
            $fieldManifest['name'] = $fieldName;
            $fields->push($this->fieldBuilder->build($table, $fieldManifest));
        }
        return $fields;
    }
}
