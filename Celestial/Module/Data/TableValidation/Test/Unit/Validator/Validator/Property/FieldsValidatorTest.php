<?php

namespace Celestial\Module\Data\TableValidation\Test\Unit\Validator\Validator\Property;

use Celestial\Module\Data\TableValidation\Test\UnitTest;
use Celestial\Module\Data\TableValidation\Validator\Validator\Property\FieldsValidator;

class FieldsValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$validationModule = $this->mockValidationModule();

		$this->dependencyManager->expects($this->once())
			->method('getValidationModule')
			->will($this->returnValue($validationModule));

		new FieldsValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayContainingTableFields()
	{
		$sampleTableFields = array(
			'field1' => (object)array(),
			'field2' => (object)array()
		);

		$validator = new FieldsValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

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
			'tableFields' => $sampleTableFields
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateOptionsReturnsErrorIfTableFieldsNotGiven()
	{
		$validator = new FieldsValidator($this->dependencyManager);

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
				'message' => 'Missing `tableFields` in options given to validator for validator property `fields`',
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

	public function testValidateThrowsExceptionIfOptionsValidationFails()
	{
		$validator = new FieldsValidator($this->dependencyManager);

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
				'message' => 'Missing `tableFields` in options given to validator for validator property `fields`',
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
			'Invalid options given to validator for validator property `fields`'
		);

		$validator->validate(array(), array());
	}

	public function testFieldsMustNotBeNumber()
	{
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validator = new FieldsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator fields must be an array',
				'children' => null
			))
			->willReturn($validationError);

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$result = $validator->validate(13, array('tableFields' => array()));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldsMustNotBeString()
	{
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validator = new FieldsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator fields must be an array',
				'children' => null
			))
			->willReturn($validationError);

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$result = $validator->validate('invalidFields', array('tableFields' => array()));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldsMustNotBeAnArray()
	{
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validator = new FieldsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator fields must be an array',
				'children' => null
			))
			->willReturn($validationError);

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$result = $validator->validate(array(), array('tableFields' => array()));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldsMustNotBeAnEmptyObject()
	{
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validator = new FieldsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator fields must not be empty',
				'children' => null
			))
			->willReturn($validationError);

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$result = $validator->validate((object)array(), array('tableFields' => array()));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldsMustNotContainFieldsNotListedInTableFields()
	{
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validator = new FieldsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Field `invalidField` not found in table manifest',
				'children' => null
			))
			->willReturn($validationError);

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$sampleFields = (object)array('validField', 'invalidField');
		$sampleOptions = array('tableFields' => array('validField' => (object)array()));

		$result = $validator->validate($sampleFields, $sampleOptions);

		$this->assertSame($validationResult, $result);
	}

	public function testFieldsMayBeAnArrayContainingFieldsListedInTableFields()
	{
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$optionsResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$validator = new FieldsValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsResult),
				array($validationResultProperties, $validationResult)
			));

		$this->validationModule->expects($this->never())
			->method('buildValidationError');

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsResult->expects($this->never())
			->method('pushError');

		$optionsResult->expects($this->never())
			->method('pushErrors');

		$optionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$sampleValidatorFields = (object)array('field1', 'field2');
		$sampleOptions = array(
			'tableFields' => array(
				'field1' => (object)array(),
				'field2' => (object)array(),
				'field3' => (object)array()
			)
		);

		$result = $validator->validate($sampleValidatorFields, $sampleOptions);

		$this->assertSame($validationResult, $result);
	}
}
