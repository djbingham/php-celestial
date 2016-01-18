<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Join;

use Sloth\Module\Data\TableValidation\Validator\Join\StructureValidator;
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

	public function testJoinMustNotBeArray()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join must be an object');

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMustNotBeString()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join must be an object');

		$result = $validator->validate('invalid field');

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMustNotBeNumber()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join must be an object');

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMustNotBeBoolean()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join must be an object');

		$result = $validator->validate(true);

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMustHaveTypeProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Missing required property `type`');

		$result = $validator->validate((object)array(
			'table' => 'tableName',
			'joins' => (object)array()
		));

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMustHaveTableProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Missing required property `table`');

		$result = $validator->validate((object)array(
			'type' => 'oneToOne',
			'joins' => (object)array()
		));

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMustHaveJoinsProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Missing required property `joins`');

		$result = $validator->validate((object)array(
			'type' => 'oneToOne',
			'table' => 'tableName'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMayNotHaveUnrecognisedProperties()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Unrecognised property `invalidPropertyName` defined');

		$result = $validator->validate((object)array(
			'type' => 'oneToOne',
			'table' => 'tableName',
			'joins' => (object)array(),
			'invalidPropertyName' => true
		));

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMayHaveOnlyRequiredProperties()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'type' => 'oneToOne',
			'table' => 'tableName',
			'joins' => (object)array(),
		));

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMayHaveViaProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'type' => 'oneToOne',
			'table' => 'tableName',
			'joins' => (object)array(),
			'via' => 'otherTableName'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMayHaveOnInsertProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'type' => 'oneToOne',
			'table' => 'tableName',
			'joins' => (object)array(),
			'onInsert' => 'insert'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMayHaveOnUpdateProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'type' => 'oneToOne',
			'table' => 'tableName',
			'joins' => (object)array(),
			'onUpdate' => 'update'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMayHaveOnDeleteProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'type' => 'oneToOne',
			'table' => 'tableName',
			'joins' => (object)array(),
			'onUpdate' => 'delete'
		));

		$this->assertSame($validationResult, $result);
	}
}
