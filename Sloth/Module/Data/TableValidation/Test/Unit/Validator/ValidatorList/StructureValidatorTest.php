<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\ValidatorList;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\ValidatorList\StructureValidator;

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

	public function testValidatorListMustNotBeAnObject()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Validator list must be an array');

		$result = $validator->validate((object)array());

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorListMustNotBeString()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Validator list must be an array');

		$result = $validator->validate('invalid join');

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorListMustNotBeNumber()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Validator list must be an array');

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorListMustNotBeBoolean()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Validator list must be an array');

		$result = $validator->validate(true);

		$this->assertSame($validationResult, $result);
	}

	public function testValidatorMayBeAnArray()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate(array(
			'myFirstValidatorAlias' => 'firstValidator',
			'mySecondValidatorAlias' => 'secondValidator'
		));

		$this->assertSame($validationResult, $result);
	}
}
