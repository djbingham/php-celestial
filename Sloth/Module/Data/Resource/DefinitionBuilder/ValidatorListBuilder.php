<?php
namespace Sloth\Module\Data\Resource\DefinitionBuilder;

use Sloth\Module\Data\Resource\Definition;

class ValidatorListBuilder
{
	public function build(array $validatorListManifest)
	{
		$validatorList = new Definition\Resource\ValidatorList();

		foreach ($validatorListManifest as $validatorManifest) {
			$validator = new Definition\Resource\Validator();
			$validator->rule = $validatorManifest->rule;
			$validator->attributes = $validatorManifest->attributes;

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
