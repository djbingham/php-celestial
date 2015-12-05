<?php
namespace Sloth\Module\Validation;

use Sloth\Base\AbstractModuleFactory;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Validation\Face\ValidatorInterface;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		$module = new ValidationModule();

		$module->setResultFactory($this->instantiateResultFactory());

		foreach ($this->options['validators'] as $validatorName => $validatorClass) {
			$validator = $this->instantiateValidator($validatorClass, $module);
			$module->setValidator($validatorName, $validator);
		}

		return $module;
	}

	protected function validateOptions()
	{
		$required = array('validators');

		$missing = array_diff($required, array_keys($this->options));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Validator module: ' . implode(', ', $missing)
			);
		}

		if (!is_array($this->options['validators']) || empty($this->options['validators'])) {
			throw new InvalidArgumentException('No validators given in options for Validator module');
		}
	}

	protected function instantiateResultFactory()
	{
		return new ResultFactory();
	}

	/**
	 * @param string $validatorClass
	 * @param ValidationModule $validationModule
	 * @return ValidatorInterface
	 */
	protected function instantiateValidator($validatorClass, ValidationModule $validationModule)
	{
		return new $validatorClass($validationModule);
	}
}
