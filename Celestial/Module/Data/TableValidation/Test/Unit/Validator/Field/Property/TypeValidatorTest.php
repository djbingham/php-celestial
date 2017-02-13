<?php
namespace Celestial\Module\Data\TableValidation\Test\Unit\Validator\Field\Property;

use Celestial\Module\Data\TableValidation\Test\UnitTest;
use Celestial\Module\Data\TableValidation\Validator\Field\Property\TypeValidator;

class TypeValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);

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

	public function testFieldTypeMustNotBeArray()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$structureValidator,
			$validationResult,
			'Field type must be a string'
		);

		$result = $structureValidator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeMustNotBeBoolean()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$structureValidator,
			$validationResult,
			'Field type must be a string'
		);

		$result = $structureValidator->validate(false);

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeMustNotBeNumber()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$structureValidator,
			$validationResult,
			'Field type must be a string'
		);

		$result = $structureValidator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeMustNotBeObject()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$structureValidator,
			$validationResult,
			'Field type must be a string'
		);

		$result = $structureValidator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeMustNotBeUnrecognised()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$structureValidator,
			$validationResult,
			'Invalid data type given. Must be text, number or boolean'
		);

		$result = $structureValidator->validate('Not a real field type');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeMayBeTextWithMaximumLength()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate('text');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeTextMustNotHaveNonNumericMaximumLength()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$structureValidator,
			$validationResult,
			'Invalid declaration of text data type. Should be similar to "text(100)"'
		);

		$result = $structureValidator->validate('text(abc)');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeMayBeNumberWithMaximumIntegerLength()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate('number(11)');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeMayBeNumberWithMaximumIntegerAndDecimalLengths()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate('number(8,2)');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeNumberMustHaveMaximumLength()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$structureValidator,
			$validationResult,
			'Invalid declaration of number data type. Should be similar to "number(11)" or "number(4,2)"'
		);

		$result = $structureValidator->validate('number');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeNumberMustNotHaveNonNumericMaximumLength()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$structureValidator,
			$validationResult,
			'Invalid declaration of number data type. Should be similar to "number(11)" or "number(4,2)"'
		);

		$result = $structureValidator->validate('number(abc)');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeMayBeBoolean()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate('boolean');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldTypeBooleanMustNotHaveMaximumLength()
	{
		$structureValidator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$structureValidator,
			$validationResult,
			'Invalid declaration of boolean data type. Should be "boolean"'
		);

		$result = $structureValidator->validate('boolean(12)');

		$this->assertSame($validationResult, $result);
	}
}
