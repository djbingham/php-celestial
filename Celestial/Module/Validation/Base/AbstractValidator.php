<?php
namespace Celestial\Module\Validation\Base;

use Celestial\Module\Validation\Face\ValidatorInterface;
use Celestial\Module\Validation\ValidationModule;

abstract class AbstractValidator implements ValidatorInterface
{
	/**
	 * @var ValidationModule
	 */
	private $validationModule;

	public function __construct(ValidationModule $validationModule)
	{
		$this->validationModule = $validationModule;
	}

	protected function buildResult(array $properties = array())
	{
		if (!array_key_exists('validator', $properties)) {
			$properties['validator'] = $this;
		}

		return $this->validationModule->buildValidationResult($properties);
	}

	protected function buildError($message)
	{
		$properties = array(
			'message' => $message,
			'validator' => $this
		);

		return $this->validationModule->buildValidationError($properties);
	}
}
