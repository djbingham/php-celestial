<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Field;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\Validator\Field\FieldValidator;
use Sloth\Module\Data\TableValidation\Test\UnitTest;

class FieldValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$structureValidator = $this->mockStructureValidator();
		$propertyValidators = array(
			'autoIncrement' => $this->mockPropertyValidator('AutoIncrement'),
			'isUnique' => $this->mockPropertyValidator('IsUnique'),
			'name' => $this->mockPropertyValidator('Name'),
			'type' => $this->mockPropertyValidator('Type'),
			'validatorList' => $this->mockPropertyValidator('ValidatorList')
		);

		$this->dependencyManager->expects($this->once())
			->method('getFieldStructureValidator')
			->will($this->returnValue($structureValidator));

		foreach ($propertyValidators as $propertyName => $propertyValidator) {
			$methodName = 'getField' . ucfirst($propertyName) . 'Validator';

			$this->dependencyManager->expects($this->once())
				->method($methodName)
				->will($this->returnValue($propertyValidator));
		}

		new FieldValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayAndReturnsResultWithNoErrors()
	{
		$fieldValidator = new FieldValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array('validator' => $fieldValidator))
			->will($this->returnValue($validationResult));

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $fieldValidator->validateOptions(array(
			'fieldAlias' => 'myFieldAlias'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateExecutesAllSubValidators()
	{
		$sampleField = (object)array(
			'autoIncrement' => false,
			'isUnique' => true,
			'field' => 'sampleName',
			'type' => 'text(20)',
			'validators' => array()
		);

		$structureValidator = $this->mockStructureValidator();
		$propertyValidators = array(
			'autoIncrement' => $this->mockPropertyValidator('AutoIncrement'),
			'isUnique' => $this->mockPropertyValidator('IsUnique'),
			'field' => $this->mockPropertyValidator('Name'),
			'type' => $this->mockPropertyValidator('Type'),
			'validators' => $this->mockPropertyValidator('ValidatorList')
		);

		$structureValidationResult = $this->mockValidationResult();

		$this->dependencyManager->expects($this->once())
			->method('getFieldStructureValidator')
			->will($this->returnValue($structureValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleField)
			->will($this->returnValue($structureValidationResult));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		/** @var BaseValidator|\PHPUnit_Framework_MockObject_MockObject $propertyValidator */
		foreach ($propertyValidators as $propertyName => $propertyValidator) {
			$propertyValidationResult = $this->mockValidationResult();
			$propertyValidationResult->expects($this->once())
				->method('isValid')
				->will($this->returnValue(true));

			$propertyValidator->expects($this->once())
				->method('validate')
				->with($sampleField->$propertyName)
				->will($this->returnValue($propertyValidationResult));
		}

		$validator = new FieldValidator($this->dependencyManager);

		$validator->validate($sampleField);
	}

	public function testValidateReturnsErrorsFromStructureValidator()
	{
		$sampleField = (object)array(
			'autoIncrement' => false,
			'isUnique' => true,
			'field' => 'sampleName',
			'type' => 'text(20)',
			'validators' => array()
		);

		$structureValidator = $this->mockStructureValidator();
		$propertyValidators = array(
			'autoIncrement' => $this->mockPropertyValidator('AutoIncrement'),
			'isUnique' => $this->mockPropertyValidator('IsUnique'),
			'field' => $this->mockPropertyValidator('Name'),
			'type' => $this->mockPropertyValidator('Type'),
			'validators' => $this->mockPropertyValidator('ValidatorList')
		);

		$fieldValidator = new FieldValidator($this->dependencyManager);

		$structureValidationResult = $this->mockValidationResult();
		$structureErrorList = $this->mockValidationErrorList();

		$fieldValidationResult = $this->mockValidationResult();
		$fieldErrorList = $this->mockValidationErrorList();
		$fieldError = $this->mockValidationError();

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleField)
			->will($this->returnValue($structureValidationResult));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$structureValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($structureErrorList));

		/** @var BaseValidator|\PHPUnit_Framework_MockObject_MockObject $propertyValidator */
		foreach ($propertyValidators as $propertyName => $propertyValidator) {
			$propertyValidationResult = $this->mockValidationResult();

			$propertyValidationResult->expects($this->once())
				->method('isValid')
				->will($this->returnValue(true));

			$propertyValidationResult->expects($this->never())
				->method('getErrors');

			$propertyValidator->expects($this->once())
				->method('validate')
				->with($sampleField->$propertyName)
				->will($this->returnValue($propertyValidationResult));
		}

		$errorProperties = array(
			'validator' => $fieldValidator,
			'message' => 'Field structure is invalid',
			'children' => $structureErrorList
		);

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->will($this->returnValue($fieldErrorList));

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with($errorProperties)
			->will($this->returnValue($fieldError));

		$fieldErrorList->expects($this->once())
			->method('push')
			->withConsecutive($fieldError)
			->will($this->returnSelf());

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $fieldValidator,
				'errors' => $fieldErrorList
			))
			->will($this->returnValue($fieldValidationResult));

		$output = $fieldValidator->validate($sampleField);

		$this->assertSame($fieldValidationResult, $output);
	}

	public function testValidateReturnsErrorsFromPropertyValidators()
	{
		$sampleField = (object)array(
			'autoIncrement' => false,
			'isUnique' => true,
			'field' => 'sampleName',
			'type' => 'text(20)',
			'validators' => array()
		);

		$structureValidator = $this->mockStructureValidator();
		$propertyValidators = array(
			'autoIncrement' => $this->mockPropertyValidator('AutoIncrement'),
			'isUnique' => $this->mockPropertyValidator('IsUnique'),
			'field' => $this->mockPropertyValidator('Name'),
			'type' => $this->mockPropertyValidator('Type'),
			'validators' => $this->mockPropertyValidator('ValidatorList')
		);

		$fieldValidator = new FieldValidator($this->dependencyManager);

		$structureValidationResult = $this->mockValidationResult();
		$propertyErrors = array(
			'autoIncrement' => $this->mockValidationErrorList(),
			'isUnique' => $this->mockValidationErrorList(),
			'field' => $this->mockValidationErrorList(),
			'type' => $this->mockValidationErrorList(),
			'validators' => $this->mockValidationErrorList()
		);

		$fieldValidationResult = $this->mockValidationResult();
		$fieldErrorList = $this->mockValidationErrorList();
		$fieldErrors = array(
			'autoIncrement' => $this->mockValidationError(),
			'isUnique' => $this->mockValidationError(),
			'field' => $this->mockValidationError(),
			'type' => $this->mockValidationError(),
			'validators' => $this->mockValidationError()
		);

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleField)
			->will($this->returnValue($structureValidationResult));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$structureValidationResult->expects($this->never())
			->method('getErrors');

		/** @var BaseValidator|\PHPUnit_Framework_MockObject_MockObject $propertyValidator */
		foreach ($propertyValidators as $propertyName => $propertyValidator) {
			$propertyValidationResult = $this->mockValidationResult();

			$propertyValidationResult->expects($this->once())
				->method('isValid')
				->will($this->returnValue(false));

			$propertyValidationResult->expects($this->once())
				->method('getErrors')
				->will($this->returnValue($propertyErrors[$propertyName]));

			$propertyValidator->expects($this->once())
				->method('validate')
				->with($sampleField->$propertyName)
				->will($this->returnValue($propertyValidationResult));
		}

		$errorProperties = array(
			'autoIncrement' => array(
				'validator' => $fieldValidator,
				'message' => 'Value of `autoIncrement` property is invalid',
				'children' => $propertyErrors['autoIncrement']
			),
			'isUnique' => array(
				'validator' => $fieldValidator,
				'message' => 'Value of `isUnique` property is invalid',
				'children' => $propertyErrors['isUnique']
			),
			'field' => array(
				'validator' => $fieldValidator,
				'message' => 'Value of `field` property is invalid',
				'children' => $propertyErrors['field']
			),
			'type' => array(
				'validator' => $fieldValidator,
				'message' => 'Value of `type` property is invalid',
				'children' => $propertyErrors['type']
			),
			'validators' => array(
				'validator' => $fieldValidator,
				'message' => 'Value of `validators` property is invalid',
				'children' => $propertyErrors['validators']
			)
		);

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->will($this->returnValue($fieldErrorList));

		$this->validationModule->expects($this->exactly(5))
			->method('buildValidationError')
			->will($this->returnValueMap(array(
				array($errorProperties['autoIncrement'], $fieldErrors['autoIncrement']),
				array($errorProperties['isUnique'], $fieldErrors['isUnique']),
				array($errorProperties['field'], $fieldErrors['field']),
				array($errorProperties['type'], $fieldErrors['type']),
				array($errorProperties['validators'], $fieldErrors['validators'])
			)));

		$fieldErrorList->expects($this->exactly(5))
			->method('push')
			->withConsecutive(
				$fieldErrors['autoIncrement'],
				$fieldErrors['isUnique'],
				$fieldErrors['field'],
				$fieldErrors['type'],
				$fieldErrors['validators']
			)
			->will($this->returnSelf());

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $fieldValidator,
				'errors' => $fieldErrorList
			))
			->will($this->returnValue($fieldValidationResult));

		$output = $fieldValidator->validate($sampleField);

		$this->assertSame($fieldValidationResult, $output);
	}

	private function mockPropertyValidator($propertyName)
	{
		$className = ucfirst($propertyName) . 'Validator';
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Field\\Property\\' . $className;

		$methodName = 'getField' . ucfirst($propertyName) . 'Validator';

		$propertyValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method($methodName)
			->will($this->returnValue($propertyValidator));

		return $propertyValidator;
	}

	private function mockStructureValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Field\\StructureValidator';

		$structureValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getFieldStructureValidator')
			->will($this->returnValue($structureValidator));

		return $structureValidator;
	}
}
