<?php
namespace Sloth\Module\Data\TableDataValidator\Result;

use Helper\Face\ObjectListInterface;
use Sloth\Helper\ObjectListTrait;

class ExecutedValidatorList implements ObjectListInterface
{
	use ObjectListTrait;

	public function push(ExecutedValidator $item)
	{
		$this->append($item);
		return $this;
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
