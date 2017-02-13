<?php
namespace Celestial\Module\Data\TableValidation\Test\Unit\Validator\Field\Property;

use Celestial\Module\Data\TableValidation\Test\UnitTest;
use Celestial\Module\Data\TableValidation\Validator\Field\Property\IsUniqueValidator;

class IsUniqueValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$structureValidator = new IsUniqueValidator($this->dependencyManager);

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
		$structureValidator = new IsUniqueValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Must be a boolean value');

		$result = $structureValidator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeString()
	{
		$structureValidator = new IsUniqueValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Must be a boolean value');

		$result = $structureValidator->validate('invalid field');

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$structureValidator = new IsUniqueValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Must be a boolean value');

		$result = $structureValidator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$structureValidator = new IsUniqueValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Must be a boolean value');

		$result = $structureValidator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeBoolean()
	{
		$structureValidator = new IsUniqueValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate(true);

		$this->assertSame($validationResult, $result);
	}
}
