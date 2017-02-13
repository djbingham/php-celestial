<?php

namespace Celestial\Module\Data\TableValidation\Test\Unit\Validator\Validator\Property;

use Celestial\Module\Data\TableValidation\Test\UnitTest;
use Celestial\Module\Data\TableValidation\Validator\Validator\Property\OptionsValidator;

class OptionsValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$validationModule = $this->mockValidationModule();

		$this->dependencyManager->expects($this->once())
			->method('getValidationModule')
			->will($this->returnValue($validationModule));

		new OptionsValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayContainingValidationRule()
	{
		$validator = new OptionsValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('ruleName')
			->willReturn(true);

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => $errorList
			))
			->willReturn($validationResult);

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validateOptions(array(
			'rule' => 'ruleName'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateOptionsReturnsErrorIfValidationRuleNotGiven()
	{
		$validator = new OptionsValidator($this->dependencyManager);

		$optionsValidationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();
		$error = $this->mockValidationError();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Missing `rule` in options given to validator for validator property `options`',
				'children' => null
			))
			->willReturn($error);

		$errorList->expects($this->once())
			->method('push')
			->with($error)
			->willReturnSelf();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => $errorList
			))
			->willReturn($optionsValidationResult);

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validateOptions(array());

		$this->assertSame($optionsValidationResult, $result);
	}

	public function testValidateOptionsReturnsErrorIfValidationRuleIsNotFound()
	{
		$validator = new OptionsValidator($this->dependencyManager);

		$optionsValidationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();
		$error = $this->mockValidationError();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('invalidRuleName')
			->willReturn(false);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Unrecognised rule in options given to validator for validator property `options`',
				'children' => null
			))
			->willReturn($error);

		$errorList->expects($this->once())
			->method('push')
			->with($error)
			->willReturnSelf();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => $errorList
			))
			->willReturn($optionsValidationResult);

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validateOptions(array('rule' => 'invalidRuleName'));

		$this->assertSame($optionsValidationResult, $result);
	}

	public function testValidateThrowsExceptionIfOptionsValidationFails()
	{
		$validator = new OptionsValidator($this->dependencyManager);

		$optionsValidationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();
		$error = $this->mockValidationError();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Missing `rule` in options given to validator for validator property `options`',
				'children' => null
			))
			->willReturn($error);

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => $errorList
			))
			->willReturn($optionsValidationResult);

		$errorList->expects($this->once())
			->method('push')
			->with($error)
			->willReturnSelf();

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(false);

		$this->setExpectedException(
			'Celestial\Exception\InvalidArgumentException',
			'Invalid options given to validator for validator property `options`'
		);

		$validator->validate(array(), array());
	}

	public function testValueMustNotBeString()
	{
		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$validator = new OptionsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('ruleName')
			->willReturn(true);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator options must be an array',
				'children' => null
			))
			->willReturn($validationError);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError);

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validate('invalidValue', array('rule' => 'ruleName'));

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$validator = new OptionsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('ruleName')
			->willReturn(true);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator options must be an array',
				'children' => null
			))
			->willReturn($validationError);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError);

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validate(13, array('rule' => 'ruleName'));

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeBoolean()
	{
		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$validator = new OptionsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('ruleName')
			->willReturn(true);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator options must be an array',
				'children' => null
			))
			->willReturn($validationError);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError);

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validate(true, array('rule' => 'ruleName'));

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeAnObject()
	{
		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$validator = new OptionsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('ruleName')
			->willReturn(true);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator options must be an array',
				'children' => null
			))
			->willReturn($validationError);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError);

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validate(new \stdClass(), array('rule' => 'ruleName'));

		$this->assertSame($validationResult, $result);
	}

	public function testValidationRuleIsUsedToValidateOptionsArray()
	{
		$sampleValidatorOptions = array(
			'optionName' => 'optionValue'
		);

		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$subValidator = $this->mockValidator();
		$subValidatorResult = $this->mockValidationResult();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$validator = new OptionsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('ruleName')
			->willReturn(true);

		$this->validationModule->expects($this->once())
			->method('getValidator')
			->with('ruleName')
			->willReturn($subValidator);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->never())
			->method('buildValidationError');

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$subValidator->expects($this->once())
			->method('validateOptions')
			->with($sampleValidatorOptions)
			->willReturn($subValidatorResult);

		$subValidatorResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$subValidatorResult->expects($this->never())
			->method('getErrors');

		$validationErrorList->expects($this->never())
			->method('push');

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validate($sampleValidatorOptions, array('rule' => 'ruleName'));

		$this->assertSame($validationResult, $result);
	}

	public function testErrorsFromRuleOptionsValidationAreReturnedAsChildrenOfValidationError()
	{
		$sampleValidatorOptions = array(
			'optionName' => 'optionValue'
		);

		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$subValidator = $this->mockValidator();
		$subValidatorResult = $this->mockValidationResult();
		$subValidatorErrorList = $this->mockValidationErrorList();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$validator = new OptionsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->once())
			->method('validatorExists')
			->with('ruleName')
			->willReturn(true);

		$this->validationModule->expects($this->once())
			->method('getValidator')
			->with('ruleName')
			->willReturn($subValidator);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator options are invalid',
				'children' => $subValidatorErrorList
			))
			->willReturn($validationError);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$subValidator->expects($this->once())
			->method('validateOptions')
			->with($sampleValidatorOptions)
			->willReturn($subValidatorResult);

		$subValidatorResult->expects($this->once())
			->method('isValid')
			->willReturn(false);

		$subValidatorResult->expects($this->once())
			->method('getErrors')
			->willReturn($subValidatorErrorList);

		$result = $validator->validate($sampleValidatorOptions, array('rule' => 'ruleName'));

		$this->assertSame($validationResult, $result);
	}

	private function mockValidator()
	{
		return $this->getMockBuilder('Celestial\\Module\\Validation\\Face\\ValidatorInterface')
			->disableOriginalConstructor()
			->getMock();
	}
}
