<?php
namespace Sloth\Module\Data\TableValidation\Validator\FieldList;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class AliasValidator extends BaseValidator
{
	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($fieldAlias, array $options = array())
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}
}
