<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Join\Property;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Join\Property\TypeValidator;

class TypeValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$validator = new TypeValidator($this->dependencyManager);

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
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeBoolean()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate(false);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeString()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate('invalid type');

		$this->assertSame($validationResult, $result);
	}

	public function testTypeValueMustNotBeArray()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate((object)array('type' => array()));

		$this->assertSame($validationResult, $result);
	}

	public function testTypeValueMustNotBeBoolean()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate((object)array('type' => false));

		$this->assertSame($validationResult, $result);
	}

	public function testTypeValueMustNotBeNumber()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate((object)array('type' => 13));

		$this->assertSame($validationResult, $result);
	}

	public function testTypeValueMustNotBeObject()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate((object)array('type' => new \stdClass()));

		$this->assertSame($validationResult, $result);
	}

	public function testTypeValueMustNotBeAnUnrecognisedString()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join type must be one of the following: oneToOne, oneToMany, manyToOne, manyToMany'
		);

		$result = $validator->validate((object)array('type' => 'invalid join type'));

		$this->assertSame($validationResult, $result);
	}

	public function testTypeValueMayBeOneToOne()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('type' => 'oneToOne'));

		$this->assertSame($validationResult, $result);
	}

	public function testTypeValueMayBeOneToMany()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('type' => 'oneToMany'));

		$this->assertSame($validationResult, $result);
	}

	public function testTypeValueMayBeManyToOne()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('type' => 'manyToOne'));

		$this->assertSame($validationResult, $result);
	}

	public function testTypeValueMayBeManyToMany()
	{
		$validator = new TypeValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('type' => 'manyToMany'));

		$this->assertSame($validationResult, $result);
	}
}
