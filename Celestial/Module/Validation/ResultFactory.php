<?php
namespace Celestial\Module\Validation;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Validation\Face\ValidationResultFactoryInterface;
use Celestial\Module\Validation\Result\ValidationError;
use Celestial\Module\Validation\Result\ValidationErrorList;
use Celestial\Module\Validation\Result\ValidationResult;
use Celestial\Module\Validation\Result\ValidationResultList;

class ResultFactory implements ValidationResultFactoryInterface
{
	public function buildResultList(array $results)
	{
		$list = new ValidationResultList();

		foreach ($results as $result) {
			if (is_array($result)) {
				$result = $this->buildResult($result);
			}
			$list->pushResult($result);
		}

		return $list;
	}

	public function buildResult(array $properties)
	{
		$this->validateResultProperties($properties);

		$properties = $this->padResultProperties($properties);
		$properties = $this->formatResultProperties($properties);

		$result = new ValidationResult($properties);

		return $result;
	}

	public function buildErrorList(array $errors)
	{
		$list = new ValidationErrorList();

		foreach ($errors as $error) {
			if (is_array($error)) {
				$error = $this->buildError($error);
			}
			$list->push($error);
		}

		return $list;
	}

	public function buildError(array $properties)
	{
		$this->validateErrorProperties($properties);

		$properties = $this->padErrorProperties($properties);

		return new ValidationError($properties);
	}

	private function validateResultProperties(array $properties)
	{
		if (array_key_exists('errors', $properties)) {
			if (!is_array($properties['errors']) && !($properties['errors'] instanceof ValidationErrorList)) {
				throw new InvalidArgumentException(
					'Validation result property `errors` must be either an array or ValidationErrorList instance.'
				);
			}
		}
	}

	private function padResultProperties(array $properties)
	{
		if (!array_key_exists('errors', $properties)) {
			$properties['errors'] = new ValidationErrorList();
		}

		return $properties;
	}

	private function formatResultProperties(array $properties)
	{
		if (!($properties['errors'] instanceof ValidationErrorList)) {
			$properties['errors'] = $this->buildErrorList($properties['errors']);
		}

		return $properties;
	}

	private function validateErrorProperties(array $properties)
	{
		if (array_key_exists('message', $properties)) {
			if (!is_string($properties['message'])) {
				throw new InvalidArgumentException('Validation error property `message` must be a string.');
			}
		} else {
			throw new InvalidArgumentException('Validation error is missing a required property `message`.');
		}
	}

	private function padErrorProperties(array $properties)
	{
		if (!array_key_exists('message', $properties)) {
			$properties['message'] = '';
		}

		if (!array_key_exists('children', $properties) || $properties['children'] === null) {
			$properties['children'] = new ValidationErrorList();
		}

		return $properties;
	}
}
