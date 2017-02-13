<?php
namespace Celestial\Module\Data\TableValidation\Test\Unit\Validator\TableManifest;

use Celestial\Module\Data\TableValidation\Test\UnitTest;
use Celestial\Module\Data\TableValidation\Validator\TableManifest\TableManifestValidator;

class TableManifestValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$validationModule = $this->mockValidationModule();
		$structureValidator = $this->mockTableManifestStructureValidator();
		$fieldListValidator = $this->mockFieldListValidator();
		$joinListValidator = $this->mockJoinListValidator();
		$validatorListValidator = $this->mockValidatorListValidator();

		$this->dependencyManager->expects($this->once())
			->method('getValidationModule')
			->will($this->returnValue($validationModule));

		$this->dependencyManager->expects($this->once())
			->method('getTableManifestStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldListValidator')
			->will($this->returnValue($fieldListValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinListValidator')
			->will($this->returnValue($joinListValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorListValidator')
			->will($this->returnValue($validatorListValidator));

		new TableManifestValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayAndReturnsResultWithNoErrors()
	{
		$validator = new TableManifestValidator($this->dependencyManager);

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

	public function testValidateExecutesAllSubValidators()
	{
		$sampleManifest = (object)array(
			'fields' => (object)array(),
			'links' => (object)array(),
			'validators' => (object)array()
		);

		$structureValidator = $this->mockTableManifestStructureValidator();
		$fieldListValidator = $this->mockFieldListValidator();
		$joinListValidator = $this->mockJoinListValidator();
		$validatorListValidator = $this->mockValidatorListValidator();

		$structureValidationResult = $this->mockValidationResult();
		$fieldListValidationResult = $this->mockValidationResult();
		$joinListValidationResult = $this->mockValidationResult();
		$validatorListValidationResult = $this->mockValidationResult();

		$validationResultList = $this->mockValidationResultList();
		$flattenedValidationResultList = $this->mockValidationResultList();

		$this->dependencyManager->expects($this->once())
			->method('getTableManifestStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldListValidator')
			->will($this->returnValue($fieldListValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinListValidator')
			->will($this->returnValue($joinListValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorListValidator')
			->will($this->returnValue($validatorListValidator));

		$this->validationModule->expects($this->once())
			->method('buildValidationResultList')
			->willReturn($validationResultList);

		$validationResultList->expects($this->exactly(4))
			->method('pushResult')
			->withConsecutive(
				$structureValidationResult,
				$fieldListValidationResult,
				$joinListValidationResult,
				$validatorListValidationResult
			)
			->willReturnSelf();

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleManifest)
			->will($this->returnValue($structureValidationResult));

		$fieldListValidator->expects($this->once())
			->method('validate')
			->with($sampleManifest->fields)
			->will($this->returnValue($fieldListValidationResult));

		$joinListValidator->expects($this->once())
			->method('validate')
			->with($sampleManifest->links)
			->will($this->returnValue($joinListValidationResult));

		$validatorListValidator->expects($this->once())
			->method('validate')
			->with($sampleManifest->validators, array('tableManifest' => $sampleManifest))
			->will($this->returnValue($validatorListValidationResult));

		$this->validationModule->expects($this->once())
			->method('flattenResultList')
			->with($validationResultList)
			->willReturn($flattenedValidationResultList);

		$validator = new TableManifestValidator($this->dependencyManager);

		$validator->validate($sampleManifest);
	}

	private function mockTableManifestStructureValidator()
	{
		return $this->getMockBuilder('Celestial\\Module\\Data\\TableValidation\\Validator\\TableManifest\\StructureValidator')
			->disableOriginalConstructor()
			->getMock();
	}

	private function mockFieldListValidator()
	{
		return $this->getMockBuilder('Celestial\\Module\\Data\\TableValidation\\Validator\\FieldList\\FieldListValidator')
			->disableOriginalConstructor()
			->getMock();
	}

	private function mockJoinListValidator()
	{
		return $this->getMockBuilder('Celestial\\Module\\Data\\TableValidation\\Validator\\JoinList\\JoinListValidator')
			->disableOriginalConstructor()
			->getMock();
	}

	private function mockValidatorListValidator()
	{
		return $this->getMockBuilder('Celestial\\Module\\Data\\TableValidation\\Validator\\ValidatorList\\ValidatorListValidator')
			->disableOriginalConstructor()
			->getMock();
	}
}