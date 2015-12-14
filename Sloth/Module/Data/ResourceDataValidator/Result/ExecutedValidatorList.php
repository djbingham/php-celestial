<?php
namespace Sloth\Module\Data\ResourceDataValidator\Result;

use Helper\Face\ObjectListInterface;
use Sloth\Helper\ObjectListTrait;
use Sloth\Module\Data\Resource\Definition\Resource\Validator as ResourceValidator;
use Sloth\Module\Data\Table\Definition\Table\Validator as TableValidator;
use Sloth\Module\Validation\Result\ValidationErrorList;

class ExecutedValidatorList implements ObjectListInterface
{
	use ObjectListTrait;

	public function push(ExecutedValidator $item)
	{
		$this->append($item);
		return $this;
	}

	public function isValid()
	{
		return $this->getFailedValidators()->length() === 0;
	}

	public function getFailedValidators()
	{
		$failedValidators = new self();

		/** @var ExecutedValidator $executedValidator */
		foreach ($this->items as $executedValidator) {
			if (!$executedValidator->getResult()->isValid()) {
				$failedValidators->push($executedValidator);
			}
		}

		return $failedValidators;
	}

	public function getErrors()
	{
		$errors = new ValidationErrorList();

		/** @var ExecutedValidator $failedValidator */
		foreach ($this->getFailedValidators() as $failedValidator) {
			foreach ($failedValidator->getResult()->getErrors() as $error) {
				$errors->push($error);
			}
		}

		return $errors;
	}

	public function getByFieldName($fieldName)
	{
		$matched = new self();

		/** @var ExecutedValidator $executedValidator */
		foreach ($this->items as $executedValidator) {
			$validatorDefinition = $executedValidator->getDefinition();
			$fields = array();

			if ($validatorDefinition instanceof ResourceValidator) {
				$fields = $validatorDefinition->attributes;
			} elseif ($validatorDefinition instanceof TableValidator) {
				$fields = $validatorDefinition->fields;
			}

			if (in_array($fieldName, (array)$fields)) {
				$matched->push($executedValidator);
			}
		}

		return $matched;
	}
}
