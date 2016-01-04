<?php

namespace Sloth\Module\Data\TableValidation\Test;

require_once __DIR__ . '/bootstrap.php';

abstract class UnitTest extends \PHPUnit_Framework_TestCase
{
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
}
