<?php
namespace Celestial\Module\Data\TableValidation\Test\Unit\Validator\JoinList;

use Celestial\Module\Data\TableValidation\Test\UnitTest;
use Celestial\Module\Data\TableValidation\Validator\JoinList\StructureValidator;

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

	public function testJoinListMustNotBeArray()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join list must be an object');

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testJoinListMustNotBeString()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join list must be an object');

		$result = $validator->validate('invalid join');

		$this->assertSame($validationResult, $result);
	}

	public function testJoinListMustNotBeNumber()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join list must be an object');

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testJoinListMustNotBeBoolean()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join list must be an object');

		$result = $validator->validate(true);

		$this->assertSame($validationResult, $result);
	}

	public function testJoinMayBeAnObject()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'myFirstJoinAlias' => 'firstJoin',
			'mySecondJoinAlias' => 'secondJoin'
		));

		$this->assertSame($validationResult, $result);
	}
}
