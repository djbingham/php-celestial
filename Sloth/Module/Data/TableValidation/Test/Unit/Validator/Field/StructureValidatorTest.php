<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Field;

use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Data\TableValidation\Validator\Field\StructureValidator;
use Sloth\Module\Validation\ValidationModule;
use Sloth\Module\Data\TableValidation\Test\UnitTest;

class StructureValidatorTest extends UnitTest
{
	/**
	 * @var DependencyManager|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $dependencyManager;

	/**
	 * @var ValidationModule|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $validationModule;

	public function setUp()
	{
		parent::setUp();

		$this->dependencyManager = $this->mockDependencyManager();
		$this->validationModule = $this->mockValidationModule();

		$this->dependencyManager->expects($this->once())
			->method('getValidationModule')
			->will($this->returnValue($this->validationModule));
	}

	public function testValidateOptionsReturnsValidationResultWithoutErrors()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);

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

	public function testFieldMustNotBeArray()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Field must be an object');

		$result = $structureValidator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustNotBeString()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Field must be an object');

		$result = $structureValidator->validate('invalid field');

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustNotBeNumber()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Field must be an object');

		$result = $structureValidator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustNotBeBoolean()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Field must be an object');

		$result = $structureValidator->validate(true);

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustHaveNameProperty()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Missing required property `name`');

		$result = $structureValidator->validate((object)array(
			'type' => 'text(32)'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMustHaveTypeProperty()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Missing required property `type`');

		$result = $structureValidator->validate((object)array(
			'name' => 'fieldName'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayNotHaveUnrecognisedProperties()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Unrecognised property `invalidPropertyName` defined');

		$result = $structureValidator->validate((object)array(
			'name' => 'fieldName',
			'type' => 'text(32)',
			'invalidPropertyName' => true
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayHaveOnlyRequiredProperties()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate((object)array(
			'name' => 'fieldName',
			'type' => 'text(32)'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayHaveAutoIncrementProperty()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate((object)array(
			'name' => 'fieldName',
			'type' => 'text(32)',
			'autoIncrement' => false
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayHaveIsUniqueProperty()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate((object)array(
			'name' => 'fieldName',
			'type' => 'text(32)',
			'isUnique' => false
		));

		$this->assertSame($validationResult, $result);
	}

	public function testFieldMayHaveValidatorsProperty()
	{
		$structureValidator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate((object)array(
			'name' => 'fieldName',
			'type' => 'text(32)',
			'validators' => array()
		));

		$this->assertSame($validationResult, $result);
	}

	private function setupMockExpectationsForSingleError(
		StructureValidator $validator,
		\PHPUnit_Framework_MockObject_MockObject $result,
		$errorMessage
	) {
		$errorList = $this->mockValidationErrorList();
		$error = $this->mockValidationError();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->will($this->returnValue($errorList));

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => $errorMessage,
				'children' => null
			))
			->will($this->returnValue($error));

		$errorList->expects($this->once())
			->method('push')
			->with($error)
			->will($this->returnSelf());

		// result.pushError should not be called, since we pushed directly onto errorList
		$result->expects($this->never())
			->method('pushError');

		// result.pushErrorList should not be called, since we pushed directly onto errorList
		$result->expects($this->never())
			->method('pushErrors');

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => $errorList
			))
			->will($this->returnValue($result));

		return $result;
	}

	private function setupMockExpectationsForNoErrors(
		StructureValidator $validator,
		\PHPUnit_Framework_MockObject_MockObject $result
	) {
		$errorList = $this->mockValidationErrorList();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->will($this->returnValue($errorList));

		$this->validationModule->expects($this->never())
			->method('buildValidationError');

		$errorList->expects($this->never())
			->method('push');

		$result->expects($this->never())
			->method('pushError');

		$result->expects($this->never())
			->method('pushErrors');

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => $errorList
			))
			->will($this->returnValue($result));

		return $result;
	}
}
