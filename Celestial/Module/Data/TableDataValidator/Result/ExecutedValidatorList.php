<?php
namespace Celestial\Module\Data\TableDataValidator\Result;

use Celestial\Helper\Face\ObjectListInterface;
use Celestial\Helper\ObjectListTrait;
use Celestial\Module\Validation\Result\ValidationErrorList;

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
			if (in_array($fieldName, (array)$executedValidator->getDefinition()->fields)) {
				$matched->push($executedValidator);
			}
		}

		return $matched;
	}
}
