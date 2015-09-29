<?php
namespace Sloth\Module\Resource\DefinitionBuilder;

use Sloth\Module\Resource\Definition;

class ValidatorListBuilder
{
    public function build(array $validatorManifest)
    {
        return new Definition\ValidatorList();
    }
}
