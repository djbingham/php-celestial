<?php


namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\FileValidator;

class FileValidatorTest extends UnitTest
{
	private static $testFileName = 'TestFile.json';

	public function tearDown()
	{
		if (file_exists(self::$testFileName)) {
			unlink(self::$testFileName);
		}
	}

	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$validationModule = $this->mockValidationModule();
		$manifestValidator = $this->mockTableManifestValidator();

		$this->dependencyManager->expects($this->once())
			->method('getValidationModule')
			->will($this->returnValue($validationModule));

		$this->dependencyManager->expects($this->once())
			->method('getTableManifestValidator')
			->will($this->returnValue($manifestValidator));

		new FileValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayAndReturnsResultWithNoErrors()
	{
		$validator = new FileValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array('validator' => $validator))
			->will($this->returnValue($validationResult));

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validateOptions(array(
			'fieldAlias' => 'myFieldAlias'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testManifestFileMustExist()
	{
		$validator = new FileValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Manifest file not found at `No File.json`'
		);

		$result = $validator->validate('No File.json');

		$this->assertSame($validationResult, $result);
	}

	public function testManifestFileMustContainJson()
	{
		file_put_contents('TestFile.json', 'Invalid file contents');

		$validator = new FileValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Manifest is in an invalid format (JSON required)'
		);

		$result = $validator->validate('TestFile.json');

		$this->assertSame($validationResult, $result);

		unlink('TestFile.json');
	}

	public function testManifestValidatorIsExecuted()
	{
		$manifest = (object)array();
		file_put_contents('TestFile.json', json_encode($manifest));

		$manifestValidator = $this->mockTableManifestValidator();
		$manifestValidationResult = $this->mockValidationResult();

		$this->dependencyManager->expects($this->once())
			->method('getTableManifestValidator')
			->will($this->returnValue($manifestValidator));

		$manifestValidator->expects($this->once())
			->method('validate')
			->with($manifest)
			->willReturn($manifestValidationResult);

		$manifestValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validator = new FileValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate('TestFile.json');

		$this->assertSame($validationResult, $result);

		unlink('TestFile.json');
	}

	public function testErrorsFromManifestValidatorAreReturned()
	{
		$manifest = (object)array();
		file_put_contents('TestFile.json', json_encode($manifest));

		$manifestValidator = $this->mockTableManifestValidator();
		$manifestValidationResult = $this->mockValidationResult();
		$manifestValidationErrors = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getTableManifestValidator')
			->will($this->returnValue($manifestValidator));

		$manifestValidator->expects($this->once())
			->method('validate')
			->with($manifest)
			->willReturn($manifestValidationResult);

		$manifestValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(false);

		$manifestValidationResult->expects($this->once())
			->method('getErrors')
			->willReturn($manifestValidationErrors);

		$validator = new FileValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate('TestFile.json');

		$this->assertSame($validationResult, $result);

		unlink('TestFile.json');
	}

	private function mockTableManifestValidator()
	{
		return $this->getMockBuilder('Sloth\\Module\\Data\\TableValidation\\Validator\\TableManifest\\TableManifestValidator')
			->disableOriginalConstructor()
			->getMock();
	}
}
