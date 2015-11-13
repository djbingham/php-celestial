<?php
namespace Sloth\Module\Resource\DefinitionBuilder;

use Sloth\Module\Resource\Definition;

class TableValidatorListBuilder
{
	public function build(array $validatorListManifest)
	{
		$validatorList = new Definition\Table\ValidatorList();

		foreach ($validatorListManifest as $validatorManifest) {
			$validator = new Definition\Table\Validator();
			$validator->rule = $validatorManifest->rule;
			$validator->fields = $validatorManifest->fields;

			if (property_exists($validatorManifest, 'negate')) {
				$validator->negate = $validatorManifest->negate;
			}
			if (property_exists($validatorManifest, 'options')) {
				$validator->options = $validatorManifest->options;
			}

			$validatorList->push($validator);
		}

		return $validatorList;
	}
}
