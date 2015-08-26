<?php
namespace Sloth\Module\Graph\DefinitionBuilder;

use Sloth\Module\Graph\Definition;

class ValidatorListBuilder
{
    public function build(array $validatorManifest)
    {
        return new Definition\ValidatorList();
    }
}
