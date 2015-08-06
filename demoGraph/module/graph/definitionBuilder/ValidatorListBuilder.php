<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\Definition;

class ValidatorListBuilder
{
    public function build(array $validatorManifest)
    {
        return new Definition\ValidatorList();
    }
}
