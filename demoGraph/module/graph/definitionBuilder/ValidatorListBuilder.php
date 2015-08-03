<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\ResourceDefinition;

class ValidatorListBuilder
{
    public function build(ResourceDefinition\Resource $resource, array $validatorManifest)
    {
        return new ResourceDefinition\ValidatorList();
    }
}
