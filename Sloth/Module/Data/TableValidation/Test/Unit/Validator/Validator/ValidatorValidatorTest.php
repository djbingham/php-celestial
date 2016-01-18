<?php

namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Validator;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Validator\ValidatorValidator;

class ValidatorValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$structureValidator = $this->mockStructureValidator();
		$propertyValidators = array(
			'fields' => $this->mockPropertyValidator('Fields'),
			'options' => $this->mockPropertyValidator('Options'),
			'rule' => $this->mockPropertyValidator('Rule')
		);

		$this->dependencyManager->expects($this->once())
			->method('getValidatorStructureValidator')
			->will($this->returnValue($structureValidator));

		foreach ($propertyValidators as $propertyName => $propertyValidator) {
			$methodName = 'getValidator' . ucfirst($propertyName) . 'Validator';

			$this->dependencyManager->expects($this->once())
				->method($methodName)
				->will($this->returnValue($propertyValidator));
		}

		new ValidatorValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayContainingTableManifest()
	{
		$sampleTableManifest = (object)array(
			'fields' => (object)array()
		);

		$validator = new ValidatorValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => $errorList
			))
			->willReturn($validationResult);

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validateOptions(array(
			'tableManifest' => $sampleTableManifest
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateOptionsReturnsErrorIfTableManifestNotGiven()
	{
		$validator = new ValidatorValidator($this->dependencyManager);

		$optionsValidationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();
		$error = $this->mockValidationError();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Missing `tableManifest` in options given to validator for table validator',
				'children' => null
			))
			->willReturn($error);

		$errorList->expects($this->once())
			->method('push')
			->with($error)
			->willReturnSelf();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => $errorList
			))
			->willReturn($optionsValidationResult);

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validateOptions(array());

		$this->assertSame($optionsValidationResult, $result);
	}

	public function testValidateThrowsExceptionIfOptionsValidationFails()
	{
		$validator = new ValidatorValidator($this->dependencyManager);

		$optionsValidationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();
		$error = $this->mockValidationError();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Missing `tableManifest` in options given to validator for table validator',
				'children' => null
			))
			->willReturn($error);

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => $errorList
			))
			->willReturn($optionsValidationResult);

		$errorList->expects($this->once())
			->method('push')
			->with($error)
			->willReturnSelf();

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(false);

		$this->setExpectedException(
			'Sloth\Exception\InvalidArgumentException',
			'Invalid options given to validator for validator property `options`'
		);

		$validator->validate(array(), array());
	}

	public function testValidateExecutesAllSubValidators()
	{
		$sampleTableManifest = (object)array(
			'fields' => (object)array(
				'field1' => (object)array(),
				'field2' => (object)array()
			),
			'validators' => array(
				(object)array(
					'rule' => 'comparison.equals',
					'fields' => (object)array('field1', 'field2'),
					'options' => array()
				)
			)
		);

		$sampleValidator = $sampleTableManifest->validators[0];

		$validateOptionsResult = $this->mockValidationResult();
		$validateOptionsErrorList = $this->mockValidationErrorList();

		$structureValidator = $this->mockStructureValidator();
		$structureValidationResult = $this->mockValidationResult();

		$fieldsValidator = $this->mockPropertyValidator('Fields');
		$fieldsValidationResult = $this->mockValidationResult();

		$ruleValidator = $this->mockPropertyValidator('Fields');
		$ruleValidationResult = $this->mockValidationResult();

		$optionsValidator = $this->mockPropertyValidator('Fields');
		$optionsValidationResult = $this->mockValidationResult();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorFieldsValidator')
			->will($this->returnValue($fieldsValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorOptionsValidator')
			->will($this->returnValue($optionsValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorRuleValidator')
			->will($this->returnValue($ruleValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorStructureValidator')
			->will($this->returnValue($structureValidator));

		$validator = new ValidatorValidator($this->dependencyManager);

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator)
			->will($this->returnValue($structureValidationResult));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$fieldsValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator->fields, array('tableFields' => $sampleTableManifest->fields))
			->will($this->returnValue($fieldsValidationResult));

		$fieldsValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$fieldsValidationResult->expects($this->never())
			->method('getErrors');

		$optionsValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator->options, array('rule' => $sampleValidator->rule))
			->will($this->returnValue($optionsValidationResult));

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$optionsValidationResult->expects($this->never())
			->method('getErrors');

		$ruleValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator->rule)
			->will($this->returnValue($ruleValidationResult));

		$ruleValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$ruleValidationResult->expects($this->never())
			->method('getErrors');

		$validateOptionsResultProperties = array(
			'validator' => $validator,
			'errors' => $validateOptionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($validateOptionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($validateOptionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($validateOptionsResultProperties, $validateOptionsResult),
				array($validationResultProperties, $validationResult)
			));

		$validateOptionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validator->validate($sampleValidator, array('tableManifest' => $sampleTableManifest));
	}

	public function testValidateReturnsErrorsFromStructureValidator()
	{
		$sampleTableManifest = (object)array(
			'fields' => (object)array(
				'field1' => (object)array(),
				'field2' => (object)array()
			),
			'validators' => array(
				(object)array(
					'rule' => 'comparison.equals',
					'fields' => (object)array('field1', 'field2'),
					'options' => array()
				)
			)
		);

		$sampleValidator = $sampleTableManifest->validators[0];

		$validateOptionsResult = $this->mockValidationResult();
		$validateOptionsErrorList = $this->mockValidationErrorList();

		$structureValidator = $this->mockStructureValidator();
		$structureValidationResult = $this->mockValidationResult();
		$structureErrorList = $this->mockValidationErrorList();

		$fieldsValidator = $this->mockPropertyValidator('Fields');
		$fieldsValidationResult = $this->mockValidationResult();

		$ruleValidator = $this->mockPropertyValidator('Fields');
		$ruleValidationResult = $this->mockValidationResult();

		$optionsValidator = $this->mockPropertyValidator('Fields');
		$optionsValidationResult = $this->mockValidationResult();

		$validationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();
		$fieldError = $this->mockValidationError();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorFieldsValidator')
			->will($this->returnValue($fieldsValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorOptionsValidator')
			->will($this->returnValue($optionsValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorRuleValidator')
			->will($this->returnValue($ruleValidator));

		$validator = new ValidatorValidator($this->dependencyManager);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($validateOptionsErrorList, $errorList);

		$validateOptionsResultProperties = array(
			'validator' => $validator,
			'errors' => $validateOptionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $errorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($validateOptionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($validateOptionsResultProperties, $validateOptionsResult),
				array($validationResultProperties, $validationResult)
			));

		$validateOptionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validateOptionsResult->expects($this->never())
			->method('getErrors');

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator)
			->will($this->returnValue($structureValidationResult));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$structureValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($structureErrorList));

		$fieldsValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator->fields, array('tableFields' => $sampleTableManifest->fields))
			->will($this->returnValue($fieldsValidationResult));

		$fieldsValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$fieldsValidationResult->expects($this->never())
			->method('getErrors');

		$optionsValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator->options, array('rule' => $sampleValidator->rule))
			->will($this->returnValue($optionsValidationResult));

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$optionsValidationResult->expects($this->never())
			->method('getErrors');

		$ruleValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator->rule)
			->will($this->returnValue($ruleValidationResult));

		$ruleValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$ruleValidationResult->expects($this->never())
			->method('getErrors');

		$errorProperties = array(
			'validator' => $validator,
			'message' => 'Validator structure is invalid',
			'children' => $structureErrorList
		);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with($errorProperties)
			->will($this->returnValue($fieldError));

		$errorList->expects($this->once())
			->method('push')
			->withConsecutive($fieldError)
			->will($this->returnSelf());

		$output = $validator->validate($sampleValidator, array('tableManifest' => $sampleTableManifest));

		$this->assertSame($validationResult, $output);
	}

	public function testValidateReturnsErrorsFromPropertyValidators()
	{
		$sampleTableManifest = (object)array(
			'fields' => (object)array(
				'field1' => (object)array(),
				'field2' => (object)array()
			),
			'validators' => array(
				(object)array(
					'rule' => 'comparison.equals',
					'fields' => (object)array('field1', 'field2'),
					'options' => array()
				)
			)
		);

		$sampleValidator = $sampleTableManifest->validators[0];

		$validateOptionsResult = $this->mockValidationResult();
		$validateOptionsErrorList = $this->mockValidationErrorList();

		$structureValidator = $this->mockStructureValidator();
		$structureValidationResult = $this->mockValidationResult();

		$fieldsValidator = $this->mockPropertyValidator('Fields');
		$fieldsValidationResult = $this->mockValidationResult();
		$fieldsErrorList = $this->mockValidationErrorList();
		$fieldsError = $this->mockValidationError();

		$ruleValidator = $this->mockPropertyValidator('Fields');
		$ruleValidationResult = $this->mockValidationResult();
		$ruleErrorList = $this->mockValidationErrorList();
		$ruleError = $this->mockValidationError();

		$optionsValidator = $this->mockPropertyValidator('Fields');
		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$optionsError = $this->mockValidationError();

		$validationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorFieldsValidator')
			->will($this->returnValue($fieldsValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorOptionsValidator')
			->will($this->returnValue($optionsValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorRuleValidator')
			->will($this->returnValue($ruleValidator));

		$validator = new ValidatorValidator($this->dependencyManager);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($validateOptionsErrorList, $errorList);

		$validateOptionsResultProperties = array(
			'validator' => $validator,
			'errors' => $validateOptionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $errorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($validateOptionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($validateOptionsResultProperties, $validateOptionsResult),
				array($validationResultProperties, $validationResult)
			));

		$validateOptionsResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validateOptionsResult->expects($this->never())
			->method('getErrors');

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator)
			->will($this->returnValue($structureValidationResult));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$structureValidationResult->expects($this->never())
			->method('getErrors');

		$fieldsValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator->fields, array('tableFields' => $sampleTableManifest->fields))
			->will($this->returnValue($fieldsValidationResult));

		$fieldsValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$fieldsValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($fieldsErrorList));

		$optionsValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator->options, array('rule' => $sampleValidator->rule))
			->will($this->returnValue($optionsValidationResult));

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$optionsValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($optionsErrorList));

		$ruleValidator->expects($this->once())
			->method('validate')
			->with($sampleValidator->rule)
			->will($this->returnValue($ruleValidationResult));

		$ruleValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$ruleValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($ruleErrorList));

		$errorProperties = array(
			'fields' => array(
				'validator' => $validator,
				'message' => 'Value of `fields` property is invalid',
				'children' => $fieldsErrorList
			),
			'options' => array(
				'validator' => $validator,
				'message' => 'Value of `options` property is invalid',
				'children' => $optionsErrorList
			),
			'rule' => array(
				'validator' => $validator,
				'message' => 'Value of `rule` property is invalid',
				'children' => $ruleErrorList
			)
		);

		$this->validationModule->expects($this->exactly(3))
			->method('buildValidationError')
			->will($this->returnValueMap(array(
				array($errorProperties['fields'], $fieldsError),
				array($errorProperties['options'], $optionsError),
				array($errorProperties['rule'], $ruleError)
			)));

		$errorList->expects($this->exactly(3))
			->method('push')
			->withConsecutive(
				$fieldsError,
				$optionsError,
				$ruleError
			)
			->will($this->returnSelf());

		$output = $validator->validate($sampleValidator, array('tableManifest' => $sampleTableManifest));

		$this->assertSame($validationResult, $output);
	}

	private function mockPropertyValidator($propertyName)
	{
		$className = ucfirst($propertyName) . 'Validator';
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Validator\\Property\\' . $className;

		$methodName = 'getValidator' . ucfirst($propertyName) . 'Validator';

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
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Validator\\StructureValidator';

		$structureValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorStructureValidator')
			->will($this->returnValue($structureValidator));

		return $structureValidator;
	}
}