<?php
namespace Celestial\Module\Data\TableValidation\Test\Unit\Validator\FieldList;

use Celestial\Module\Data\TableValidation\Test\UnitTest;
use Celestial\Module\Data\TableValidation\Validator\FieldList\StructureValidator;

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

	public function testFieldListMustNotBeArray()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Field list must be an object');

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testFieldListMustNotBeString()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Field list must be an object');

		$result = $validator->validate('invalid field');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldListMustNotBeNumber()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Field list must be an object');

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testFieldListMustNotBeBoolean()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Field list must be an object');

		$result = $validator->validate(true);

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayBeAnObject()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'myFirstFieldAlias' => 'firstField',
			'mySecondFieldAlias' => 'secondField'
		));

		$this->assertSame($validationResult, $result);
	}
}
