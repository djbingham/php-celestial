<?php
namespace Celestial\Module\Data\TableValidation\Test\Unit\Validator\Join\Property;

use Celestial\Module\Data\TableValidation\Test\UnitTest;
use Celestial\Module\Data\TableValidation\Validator\Join\Property\OnUpdateValidator;

class OnUpdateValidatorTest extends UnitTest
{
	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);

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
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeBoolean()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate(false);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeString()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate('invalid onUpdate value');

		$this->assertSame($validationResult, $result);
	}

	public function testOnUpdateValueMustNotBeArray()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate((object)array('onUpdate' => array()));

		$this->assertSame($validationResult, $result);
	}

	public function testOnUpdateValueMustNotBeBoolean()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate((object)array('onUpdate' => false));

		$this->assertSame($validationResult, $result);
	}

	public function testOnUpdateValueMustNotBeNumber()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate((object)array('onUpdate' => 13));

		$this->assertSame($validationResult, $result);
	}

	public function testOnUpdateValueMustNotBeObject()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate((object)array('onUpdate' => new \stdClass()));

		$this->assertSame($validationResult, $result);
	}

	public function testOnUpdateValueMustNotBeAnUnrecognisedString()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join onUpdate value must be one of the following: associate, update, ignore, reject'
		);

		$result = $validator->validate((object)array('onUpdate' => 'invalid onUpdate value'));

		$this->assertSame($validationResult, $result);
	}

	public function testOnUpdateValueMayBeAssociate()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onUpdate' => 'associate'));

		$this->assertSame($validationResult, $result);
	}

	public function testOnUpdateValueMayBeDelete()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onUpdate' => 'update'));

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeIgnore()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onUpdate' => 'ignore'));

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeReject()
	{
		$validator = new OnUpdateValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('onUpdate' => 'reject'));

		$this->assertSame($validationResult, $result);
	}
}
