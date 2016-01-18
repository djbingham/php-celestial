<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Join\Property;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Join\Property\TableValidator;

class TableValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$tableModule = $this->mockTableModule();

		$this->dependencyManager->expects($this->once())
			->method('getTableModule')
			->will($this->returnValue($tableModule));

		new TableValidator($this->dependencyManager);
	}

	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$validator = new TableValidator($this->dependencyManager);

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
		$validator = new TableValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join table value must be a string');

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeBoolean()
	{
		$validator = new TableValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join table value must be a string');

		$result = $validator->validate(false);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$validator = new TableValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join table value must be a string');

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$validator = new TableValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join table value must be a string');

		$result = $validator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeString()
	{
		$validator = new TableValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($validator, $validationResult, 'Join table value must be a string');

		$result = $validator->validate('invalid table value');

		$this->assertSame($validationResult, $result);
	}

	public function testTableValueMustNotBeArray()
	{
		$validator = new TableValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join table value must be a string'
		);

		$result = $validator->validate((object)array('table' => array()));

		$this->assertSame($validationResult, $result);
	}

	public function testTableValueMustNotBeBoolean()
	{
		$validator = new TableValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join table value must be a string'
		);

		$result = $validator->validate((object)array('table' => false));

		$this->assertSame($validationResult, $result);
	}

	public function testTableValueMustNotBeNumber()
	{
		$validator = new TableValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join table value must be a string'
		);

		$result = $validator->validate((object)array('table' => 13));

		$this->assertSame($validationResult, $result);
	}

	public function testTableValueMustNotBeObject()
	{
		$validator = new TableValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Join table value must be a string'
		);

		$result = $validator->validate((object)array('table' => new \stdClass()));

		$this->assertSame($validationResult, $result);
	}

	public function testTableValueMustNotBeAnUnrecognisedString()
	{
		$validationResult = $this->mockValidationResult();
		$tableModule = $this->mockTableModule();

		$this->dependencyManager->expects($this->once())
			->method('getTableModule')
			->will($this->returnValue($tableModule));

		$validator = new TableValidator($this->dependencyManager);

		$tableModule->expects($this->once())
			->method('exists')
			->with('tableName')
			->willReturn(false);

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'No manifest found for a table named `tableName`'
		);

		$result = $validator->validate((object)array('table' => 'tableName'));

		$this->assertSame($validationResult, $result);
	}

	public function testTableValueMayBeTheNameOfAnExistingTable()
	{
		$validationResult = $this->mockValidationResult();
		$tableModule = $this->mockTableModule();

		$this->dependencyManager->expects($this->once())
			->method('getTableModule')
			->will($this->returnValue($tableModule));

		$validator = new TableValidator($this->dependencyManager);

		$tableModule->expects($this->once())
			->method('exists')
			->with('tableName')
			->willReturn(true);

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array('table' => 'tableName'));

		$this->assertSame($validationResult, $result);
	}
}
