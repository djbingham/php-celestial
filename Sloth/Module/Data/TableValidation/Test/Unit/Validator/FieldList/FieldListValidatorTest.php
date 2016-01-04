<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\FieldList;

use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\FieldList\FieldListValidator;
use Sloth\Module\Validation\ValidationModule;

class FieldListValidatorTest extends UnitTest
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
			->willReturn($this->validationModule);
	}

	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();

		$this->dependencyManager->expects($this->once())
			->method('getFieldListStructureValidator')
			->willReturn($structureValidator);

		$this->dependencyManager->expects($this->once())
			->method('getFieldListAliasValidator')
			->willReturn($aliasValidator);

		new FieldListValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayAndReturnsResultWithNoErrors()
	{
		$fieldValidator = new FieldListValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array('validator' => $fieldValidator))
			->willReturn($validationResult);

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $fieldValidator->validateOptions(array(
			'fieldAlias' => 'myFieldAlias'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateExecutesListStructureValidator()
	{
		$sampleFieldList = (object)array();

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$fieldValidator = $this->mockFieldValidator();

		$structureValidationResult = $this->mockValidationResult();

		$this->dependencyManager->expects($this->once())
			->method('getFieldListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldValidator')
			->will($this->returnValue($fieldValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleFieldList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->never())
			->method('validate');

		$fieldValidator->expects($this->never())
			->method('validate');

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$validator = new FieldListValidator($this->dependencyManager);

		$validator->validate($sampleFieldList);
	}

	public function testValidateReturnsErrorsFromListStructureValidator()
	{
		$sampleFieldList = (object)array();

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$fieldValidator = $this->mockFieldValidator();

		$structureValidationResult = $this->mockValidationResult();
		$structureErrorList = $this->mockValidationErrorList();
		$structureError = $this->mockValidationError();

		$errorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getFieldListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldValidator')
			->will($this->returnValue($fieldValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleFieldList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->never())
			->method('validate');

		$fieldValidator->expects($this->never())
			->method('validate');

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$structureValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($structureErrorList));

		$validator = new FieldListValidator($this->dependencyManager);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Field list structure is invalid',
				'children' => $structureErrorList
			))
			->willReturn($structureError);

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$errorList->expects($this->once())
			->method('push')
			->willReturn($structureError);

		$validator->validate($sampleFieldList);
	}

	public function testValidateExecutesFieldAndAliasValidators()
	{
		$sampleFieldList = (object)array(
			'firstField' => array(
				'autoIncrement' => true,
				'isUnique' => true,
				'name' => 'firstFieldName',
				'type' => 'number(11)',
				'validators' => array()
			),
			'secondField' => array(
				'autoIncrement' => false,
				'isUnique' => false,
				'name' => 'secondFieldName',
				'type' => 'text(20)',
				'validators' => array()
			)
		);

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$fieldValidator = $this->mockFieldValidator();

		$structureValidationResult = $this->mockValidationResult();
		$firstAliasValidationResult = $this->mockValidationResult();
		$secondAliasValidationResult = $this->mockValidationResult();
		$firstFieldValidationResult = $this->mockValidationResult();
		$secondFieldValidationResult = $this->mockValidationResult();

		$this->dependencyManager->expects($this->once())
			->method('getFieldListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldValidator')
			->will($this->returnValue($fieldValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleFieldList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array('firstField', array(), $firstAliasValidationResult),
				array('secondField', array(), $secondAliasValidationResult)
			));

		$fieldValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array($sampleFieldList->firstField, array(), $firstFieldValidationResult),
				array($sampleFieldList->secondField, array(), $secondFieldValidationResult)
			));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$firstAliasValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$secondAliasValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$firstFieldValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$secondFieldValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$validator = new FieldListValidator($this->dependencyManager);

		$validator->validate($sampleFieldList);
	}

	public function testValidateReturnsFieldValidatorErrors()
	{
		$sampleFieldList = (object)array(
			'firstField' => array(
				'autoIncrement' => true,
				'isUnique' => true,
				'name' => 'firstFieldName',
				'type' => 'number(11)',
				'validators' => array()
			),
			'secondField' => array(
				'autoIncrement' => false,
				'isUnique' => false,
				'name' => 'secondFieldName',
				'type' => 'text(20)',
				'validators' => array()
			)
		);

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$fieldValidator = $this->mockFieldValidator();

		$structureValidationResult = $this->mockValidationResult();
		$firstAliasValidationResult = $this->mockValidationResult();
		$secondAliasValidationResult = $this->mockValidationResult();
		$firstFieldValidationResult = $this->mockValidationResult();
		$secondFieldValidationResult = $this->mockValidationResult();

		$firstFieldErrorList = $this->mockValidationErrorList();
		$secondFieldErrorList = $this->mockValidationErrorList();
		$firstFieldError = $this->mockValidationError();
		$secondFieldError = $this->mockValidationError();

		$errorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getFieldListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldValidator')
			->will($this->returnValue($fieldValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleFieldList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array('firstField', array(), $firstAliasValidationResult),
				array('secondField', array(), $secondAliasValidationResult)
			));

		$fieldValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array($sampleFieldList->firstField, array(), $firstFieldValidationResult),
				array($sampleFieldList->secondField, array(), $secondFieldValidationResult)
			));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$firstAliasValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$secondAliasValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$firstFieldValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$firstFieldValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($firstFieldErrorList));

		$secondFieldValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$secondFieldValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($secondFieldErrorList));

		$validator = new FieldListValidator($this->dependencyManager);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationError')
			->withConsecutive(
				array(
					array(
						'validator' => $validator,
						'message' => 'Field with alias `firstField` is invalid',
						'children' => $firstFieldErrorList
					)
				),
				array(
					array(
						'validator' => $validator,
						'message' => 'Field with alias `secondField` is invalid',
						'children' => $secondFieldErrorList
					)
				)
			)
			->willReturnOnConsecutiveCalls($firstFieldError, $secondFieldError);

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$errorList->expects($this->exactly(2))
			->method('push')
			->withConsecutive($firstFieldError, $secondFieldError)
			->willReturnSelf();

		$validator->validate($sampleFieldList);
	}

	public function testValidateReturnsAliasValidatorErrors()
	{
		$sampleFieldList = (object)array(
			'firstField' => array(
				'autoIncrement' => true,
				'isUnique' => true,
				'name' => 'firstFieldName',
				'type' => 'number(11)',
				'validators' => array()
			),
			'secondField' => array(
				'autoIncrement' => false,
				'isUnique' => false,
				'name' => 'secondFieldName',
				'type' => 'text(20)',
				'validators' => array()
			)
		);

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$fieldValidator = $this->mockFieldValidator();

		$structureValidationResult = $this->mockValidationResult();
		$firstAliasValidationResult = $this->mockValidationResult();
		$secondAliasValidationResult = $this->mockValidationResult();
		$firstFieldValidationResult = $this->mockValidationResult();
		$secondFieldValidationResult = $this->mockValidationResult();

		$firstAliasErrorList = $this->mockValidationErrorList();
		$secondAliasErrorList = $this->mockValidationErrorList();
		$firstAliasError = $this->mockValidationError();
		$secondAliasError = $this->mockValidationError();

		$errorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getFieldListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getFieldValidator')
			->will($this->returnValue($fieldValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleFieldList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array('firstField', array(), $firstAliasValidationResult),
				array('secondField', array(), $secondAliasValidationResult)
			));

		$fieldValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array($sampleFieldList->firstField, array(), $firstFieldValidationResult),
				array($sampleFieldList->secondField, array(), $secondFieldValidationResult)
			));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$firstAliasValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$firstAliasValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($firstAliasErrorList));

		$secondAliasValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$secondAliasValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($secondAliasErrorList));

		$firstFieldValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$secondFieldValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$validator = new FieldListValidator($this->dependencyManager);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationError')
			->withConsecutive(
				array(
					array(
						'validator' => $validator,
						'message' => 'Field alias `firstField` is invalid',
						'children' => $firstAliasErrorList
					)
				),
				array(
					array(
						'validator' => $validator,
						'message' => 'Field alias `secondField` is invalid',
						'children' => $secondAliasErrorList
					)
				)
			)
			->willReturnOnConsecutiveCalls($firstAliasError, $secondAliasError);

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$errorList->expects($this->exactly(2))
			->method('push')
			->withConsecutive($firstAliasError, $secondAliasError)
			->willReturnSelf();

		$validator->validate($sampleFieldList);
	}

	private function mockStructureValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\FieldList\\StructureValidator';

		$structureValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getFieldListStructureValidator')
			->will($this->returnValue($structureValidator));

		return $structureValidator;
	}

	private function mockAliasValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\FieldList\\AliasValidator';

		$structureValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getFieldListAliasValidator')
			->will($this->returnValue($structureValidator));

		return $structureValidator;
	}

	private function mockFieldValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Field\\FieldValidator';

		$fieldValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getFieldValidator')
			->will($this->returnValue($fieldValidator));

		return $fieldValidator;
	}
}
