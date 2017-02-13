<?php
namespace Celestial\Module\Data\TableValidation\Test\Unit\Validator\Field\Property;

use Celestial\Module\Data\TableValidation\Test\UnitTest;
use Celestial\Module\Data\TableValidation\Validator\Join\Property\Via\TableAliasValidator;

class TableAliasValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$validator = new TableAliasValidator($this->dependencyManager);

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
		$validator = new TableAliasValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'The alias of a table listed in join property `via` must be a string'
		);

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeBoolean()
	{
		$validator = new TableAliasValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'The alias of a table listed in join property `via` must be a string'
		);

		$result = $validator->validate(false);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$validator = new TableAliasValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'The alias of a table listed in join property `via` must be a string'
		);

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$validator = new TableAliasValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'The alias of a table listed in join property `via` must be a string'
		);

		$result = $validator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeString()
	{
		$validator = new TableAliasValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate('fieldName');

		$this->assertSame($validationResult, $result);
	}
}
