<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Field\Property;

use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Field\Property\NameValidator;
use Sloth\Module\Validation\ValidationModule;

class NameValidatorTest extends UnitTest
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
		$structureValidator = new NameValidator($this->dependencyManager);

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
		$structureValidator = new NameValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Field name must be a string');

		$result = $structureValidator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeBoolean()
	{
		$structureValidator = new NameValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Field name must be a string');

		$result = $structureValidator->validate(false);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeNumber()
	{
		$structureValidator = new NameValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Field name must be a string');

		$result = $structureValidator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testValueMustNotBeObject()
	{
		$structureValidator = new NameValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError($structureValidator, $validationResult, 'Field name must be a string');

		$result = $structureValidator->validate(new \stdClass());

		$this->assertSame($validationResult, $result);
	}

	public function testValueMayBeString()
	{
		$structureValidator = new NameValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($structureValidator, $validationResult);

		$result = $structureValidator->validate('fieldName');

		$this->assertSame($validationResult, $result);
	}

	private function setupMockExpectationsForSingleError(
		NameValidator $validator,
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
		NameValidator $validator,
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
