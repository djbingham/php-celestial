<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\TableManifest;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\TableManifest\StructureValidator;

class StructureValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$validationModule = $this->mockValidationModule();

		$this->dependencyManager->expects($this->once())
			->method('getValidationModule')
			->will($this->returnValue($validationModule));

		new StructureValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayAndReturnsResultWithNoErrors()
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

		$result = $validator->validateOptions(array(
			'fieldAlias' => 'myFieldAlias'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testManifestMustNotBeString()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Manifest structure should be an object'
		);

		$result = $validator->validate('Invalid manifest');

		$this->assertSame($validationResult, $result);
	}

	public function testManifestMustNotBeNumber()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Manifest structure should be an object'
		);

		$result = $validator->validate(13);

		$this->assertSame($validationResult, $result);
	}

	public function testManifestMustNotBeArray()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Manifest structure should be an object'
		);

		$result = $validator->validate(array());

		$this->assertSame($validationResult, $result);
	}

	public function testManifestMustNotBeBoolean()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Manifest structure should be an object'
		);

		$result = $validator->validate(true);

		$this->assertSame($validationResult, $result);
	}

	public function testManifestMustContainFieldsProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Manifest is missing required properties: fields'
		);

		$result = $validator->validate((object)array());

		$this->assertSame($validationResult, $result);
	}

	public function testManifestMayContainOnlyFields()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'fields' => array()
		));

		$this->assertSame($validationResult, $result);
	}

	public function testManifestMustNotContainAnUnrecognisedProperty()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForSingleError(
			$validator,
			$validationResult,
			'Manifest contains unrecognised properties: unexpectedProperty'
		);

		$result = $validator->validate((object)array(
			'fields' => array(),
			'unexpectedProperty' => true
		));

		$this->assertSame($validationResult, $result);
	}

	public function testManifestMayContainAllRequiredAndOptionalFields()
	{
		$validator = new StructureValidator($this->dependencyManager);
		$validationResult = $this->mockValidationResult();

		$this->setupMockExpectationsForNoErrors($validator, $validationResult);

		$result = $validator->validate((object)array(
			'fields' => array(),
			'validators' => array(),
			'links' => array()
		));

		$this->assertSame($validationResult, $result);
	}
}
