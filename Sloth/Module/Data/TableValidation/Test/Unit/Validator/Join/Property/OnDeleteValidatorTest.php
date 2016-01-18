<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Join\Property;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Join\Property\OnDeleteValidator;

class OnDeleteValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);

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
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeBoolean()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate(false);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeString()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate('invalid onDelete value');

		$this->assertSame($validationResult, $result);
	}

	public function testOnDeleteValueMustNotBeArray()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate((object)array('onDelete' => array()));

		$this->assertSame($validationResult, $result);
	}

	public function testOnDeleteValueMustNotBeBoolean()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate((object)array('onDelete' => false));

		$this->assertSame($validationResult, $result);
	}

	public function testOnDeleteValueMustNotBeNumber()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate((object)array('onDelete' => 13));

		$this->assertSame($validationResult, $result);
	}

	public function testOnDeleteValueMustNotBeObject()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate((object)array('onDelete' => new \stdClass()));

		$this->assertSame($validationResult, $result);
	}

	public function testOnDeleteValueMustNotBeAnUnrecognisedString()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onDelete value must be one of the following: associate, delete, ignore, reject'
		);

		$result = $validator->validate((object)array('onDelete' => 'invalid onDelete value'));

		$this->assertSame($validationResult, $result);
	}

	public function testOnDeleteValueMayBeAssociate()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onDelete' => 'associate'));

		$this->assertSame($validationResult, $result);
	}

	public function testOnDeleteValueMayBeDelete()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onDelete' => 'delete'));

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeIgnore()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onDelete' => 'ignore'));

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeReject()
	{
		$validator = new OnDeleteValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onDelete' => 'reject'));

		$this->assertSame($validationResult, $result);
	}
}
