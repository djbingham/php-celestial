<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Validator;

use Sloth\Module\Data\TableValidation\Validator\Validator\StructureValidator;
use Sloth\Module\Data\TableValidation\Test\UnitTest;

class StructureValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$validator = new StructureValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array('validator' => $validator))
			->will($this->returnValue($validationResult));

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validateOptions(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorMustNotBeArray()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Validator must be an object');

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorMustNotBeString()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Validator must be an object');

		$result = $validator->validate('invalid field');

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorMustNotBeNumber()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Validator must be an object');

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorMustNotBeBoolean()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Validator must be an object');

		$result = $validator->validate(true);

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorMustHaveRuleProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Missing required property `rule`');

		$result = $validator->validate((object)array(
			'fields' => (object)array('fieldName')
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorMustHaveFieldsProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Missing required property `fields`');

		$result = $validator->validate((object)array(
			'rule' => 'comparison.equals'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorMayNotHaveUnrecognisedProperties()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Unrecognised property `invalidPropertyName` defined');

		$result = $validator->validate((object)array(
			'rule' => 'comparison.equals',
			'fields' => (object)array('fieldName'),
			'invalidPropertyName' => true
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorIsNotRequiredToContainOptionalProperties()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'rule' => 'comparison.equals',
			'fields' => (object)array('fieldName')
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorMayHaveOptionsProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'rule' => 'comparison.equals',
			'fields' => (object)array('fieldName'),
			'options' => (object)array()
		));

		$this->assertSame($validationResult, $result);
	}
}
