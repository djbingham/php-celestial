<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Field;

use Sloth\Module\Data\TableValidation\Validator\Field\StructureValidator;
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

	public function testFieldMustNotBeArray()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Field must be an object');

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustNotBeString()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Field must be an object');

		$result = $validator->validate('invalid field');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustNotBeNumber()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Field must be an object');

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustNotBeBoolean()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Field must be an object');

		$result = $validator->validate(true);

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustHaveNameProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Missing required property `field`');

		$result = $validator->validate((object)array(
			'type' => 'text(32)'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustHaveTypeProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Missing required property `type`');

		$result = $validator->validate((object)array(
			'field' => 'fieldName'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayNotHaveUnrecognisedProperties()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Unrecognised property `invalidPropertyName` defined');

		$result = $validator->validate((object)array(
			'field' => 'fieldName',
			'type' => 'text(32)',
			'invalidPropertyName' => true
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayHaveOnlyRequiredProperties()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'field' => 'fieldName',
			'type' => 'text(32)'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayHaveAutoIncrementProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'field' => 'fieldName',
			'type' => 'text(32)',
			'autoIncrement' => false
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayHaveIsUniqueProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'field' => 'fieldName',
			'type' => 'text(32)',
			'isUnique' => false
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayHaveValidatorsProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'field' => 'fieldName',
			'type' => 'text(32)',
			'validators' => array()
		));

		$this->assertSame($validationResult, $result);
	}
}
