<?php
namespace Sloth\Module\Data\TableValidation\Validator\Field\Property;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Validation\Face\ValidationErrorListInterface;

class TypeValidator extends BaseValidator
{
	/**
	 * @var ValidationErrorListInterface
	 */
	private $errors;

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($value, array $options = array())
	{
		$this->errors = $this->validationModule->buildValidationErrorList();

		if (is_string($value)) {
			$this->validateTypeString($value);
		} else {
			$this->errors->push($this->buildError('Field type must be a string'));
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $this->errors
		));
	}

	private function validateTypeString($typeString)
	{
		$matches = array();
		preg_match('/^(\w+)(\(([\d\,]+)\))?$/', $typeString, $matches);

		$openingBracketPosition = strpos($typeString, '(');
		if ($openingBracketPosition > 0) {
			$dataType = substr($typeString, 0, $openingBracketPosition);
		} else {
			$dataType = $typeString;
		}
		$lengths = array();

		switch ($dataType) {
			case 'text':
				$this->validateText($typeString);
				break;
			case 'number':
				$this->validateNumber($typeString);
				break;
			case 'boolean':
				$this->validateBoolean($typeString);
				break;
			default:
				$this->errors->push($this->buildError(
					sprintf('Invalid data type given. Must be text, number or boolean')
				));
				break;
		}


		return array(
			'type' => $dataType,
			'options' => $lengths
		);
	}

	private function validateText($typeString)
	{
		$matches = array();
		if (!preg_match('/^text(\((\d+)\))?$/', $typeString, $matches)) {
			$this->errors->push($this->buildError(
				sprintf('Invalid declaration of text data type. Should be similar to "text(100)"')
			));
		}
	}

	private function validateNumber($typeString)
	{
		$matches = array();
		if (!preg_match('/^number\((\d+)(\,\d+)?\)$/', $typeString, $matches)) {
			$this->errors->push($this->buildError(
				sprintf('Invalid declaration of number data type. Should be similar to "number(11)" or "number(4,2)"')
			));
		}
	}

	private function validateBoolean($typeString)
	{
		if ($typeString !== 'boolean') {
			$this->errors->push($this->buildError(
				sprintf('Invalid declaration of boolean data type. Should be "boolean"')
			));
		}
	}
}
