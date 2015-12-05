<?php
namespace Sloth\Module\Data\Table\DefinitionBuilder;

use Sloth\Module\Data\Table\Definition;

class ValidatorListBuilder
{
	public function build(array $validatorListManifest)
	{
		$validatorList = new Definition\Table\ValidatorList();

		foreach ($validatorListManifest as $validatorManifest) {
			$validator = new Definition\Table\Validator();
			$validator->rule = $validatorManifest->rule;
			$validator->fields = $validatorManifest->fields;

			if (property_exists($validatorManifest, 'message')) {
				$validator->message = $validatorManifest->message;
			}

			if (property_exists($validatorManifest, 'options')) {
				$validator->options = $validatorManifest->options;
			}

			$validatorList->push($validator);
		}

		return $validatorList;
	}
}
