<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Join\Property;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Join\Property\OnInsertValidator;

class OnInsertValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$validator = new OnInsertValidator($this->dependencyManager);

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
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeBoolean()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate(false);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeString()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate('invalid onInsert value');

		$this->assertSame($validationResult, $result);
	}

	public function testOnInsertValueMustNotBeArray()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate((object)array('onInsert' => array()));

		$this->assertSame($validationResult, $result);
	}

	public function testOnInsertValueMustNotBeBoolean()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate((object)array('onInsert' => false));

		$this->assertSame($validationResult, $result);
	}

	public function testOnInsertValueMustNotBeNumber()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate((object)array('onInsert' => 13));

		$this->assertSame($validationResult, $result);
	}

	public function testOnInsertValueMustNotBeObject()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate((object)array('onInsert' => new \stdClass()));

		$this->assertSame($validationResult, $result);
	}

	public function testOnInsertValueMustNotBeAnUnrecognisedString()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onInsert value must be one of the following: associate, insert, ignore, reject'
		);

		$result = $validator->validate((object)array('onInsert' => 'invalid onInsert value'));

		$this->assertSame($validationResult, $result);
	}

	public function testOnInsertValueMayBeAssociate()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onInsert' => 'associate'));

		$this->assertSame($validationResult, $result);
	}

	public function testOnInsertValueMayBeDelete()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onInsert' => 'insert'));

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeIgnore()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onInsert' => 'ignore'));

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeReject()
	{
		$validator = new OnInsertValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onInsert' => 'reject'));

		$this->assertSame($validationResult, $result);
	}
}
