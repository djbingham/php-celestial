<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Field\Property;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Field\Property\AutoIncrementValidator;

class AutoIncrementValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$structureValidator = new AutoIncrementValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array('validator' => $structureValidator))
			->will($this->returnValue($validationResult));

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $structureValidator->validateOptions(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeArray()
	{
		$structureValidator = new AutoIncrementValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Must be a boolean value');

		$result = $structureValidator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeString()
	{
		$structureValidator = new AutoIncrementValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Must be a boolean value');

		$result = $structureValidator->validate('invalid field');

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$structureValidator = new AutoIncrementValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Must be a boolean value');

		$result = $structureValidator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$structureValidator = new AutoIncrementValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Must be a boolean value');

		$result = $structureValidator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeBoolean()
	{
		$structureValidator = new AutoIncrementValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate(true);

		$this->assertSame($validationResult, $result);
	}
}
