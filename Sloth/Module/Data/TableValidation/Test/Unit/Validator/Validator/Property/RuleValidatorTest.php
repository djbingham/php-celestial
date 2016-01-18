<?php

namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Validator\Property;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Validator\Property\RuleValidator;

class RuleValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$validator = new RuleValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator
			))
			->will($this->returnValue($validationResult));

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validateOptions(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeArray()
	{
		$validator = new RuleValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Validation rule must be a string, matching a validator defined in application configuration');

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeBoolean()
	{
		$validator = new RuleValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Validation rule must be a string, matching a validator defined in application configuration'
		);

		$result = $validator->validate(false);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$validator = new RuleValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Validation rule must be a string, matching a validator defined in application configuration'
		);

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$validator = new RuleValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Validation rule must be a string, matching a validator defined in application configuration'
		);

		$result = $validator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustBeFromConfiguredRulesList()
	{
		$validator = new RuleValidator($this->dependencyManager);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('invalidRule')
			->willReturn(false);

		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Validation rule `invalidRule` not found in application configuration'
		);

		$result = $validator->validate('invalidRule');

		$this->assertSame($validationResult, $result);
	}

	public function testValidateReturnsWithoutErrorsIfValueIsValid()
	{
		$validator = new RuleValidator($this->dependencyManager);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('comparison.equals')
			->willReturn(true);

		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate('comparison.equals');

		$this->assertSame($validationResult, $result);
	}
}
