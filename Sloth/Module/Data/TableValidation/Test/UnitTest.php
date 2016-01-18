<?php

namespace Sloth\Module\Data\TableValidation\Test;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Validation\ValidationModule;

require_once __DIR__ . '/bootstrap.php';

abstract class UnitTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var DependencyManager|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $dependencyManager;

	/**
	 * @var ValidationModule|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $validationModule;

	public function setUp()
	{
		$this->dependencyManager = $this->mockDependencyManager();
		$this->validationModule = $this->mockValidationModule();

		$this->dependencyManager->expects($this->any())
			->method('getValidationModule')
			->will($this->returnValue($this->validationModule));
	}

	public function rootDir()
	{
		return dirname(__DIR__);
	}

	protected function mockDependencyManager()
	{
		return $this->getMockBuilder('Sloth\\Module\\Data\\TableValidation\\DependencyManager')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockTableModule()
	{
		return $this->getMockBuilder('Sloth\\Module\\Data\\Table\\TableModule')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockValidationModule()
	{
		return $this->getMockBuilder('Sloth\\Module\\Validation\\ValidationModule')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockValidationResult()
	{
		return $this->getMockBuilder('Sloth\\Module\\Validation\\Result\\ValidationResult')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockValidationResultList()
	{
		return $this->getMockBuilder('Sloth\\Module\\Validation\\Result\\ValidationResultList')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockValidationError()
	{
		return $this->getMockBuilder('Sloth\\Module\\Validation\\Result\\ValidationError')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockValidationErrorList()
	{
		return $this->getMockBuilder('Sloth\\Module\\Validation\\Result\\ValidationErrorList')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function setupMockExpectationsForSingleError(
		BaseValidator $validator,
		\PHPUnit_Framework_MockObject_MockObject $result,
		$errorMessage,
        $childErrorList = null
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
				'children' => $childErrorList
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

	protected function setupMockExpectationsForNoErrors(
		BaseValidator $validator,
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
