<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\ValidatorList;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\ValidatorList\ValidatorListValidator;

class ValidatorListValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$validationModule = $this->mockValidationModule();
		$structureValidator = $this->mockStructureValidator();
		$validator = $this->mockValidatorValidator();

		$this->dependencyManager->expects($this->once())
			->method('getValidationModule')
			->willReturn($validationModule);

		$this->dependencyManager->expects($this->once())
			->method('getValidatorListStructureValidator')
			->willReturn($structureValidator);

		$this->dependencyManager->expects($this->once())
			->method('getValidatorValidator')
			->willReturn($validator);

		new ValidatorListValidator($this->dependencyManager);
	}

	public function testValidateOptionsReturnsErrorIfOptionsDoNotContainTableManifest()
	{
		$validator = new ValidatorListValidator($this->dependencyManager);

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
				'message' => 'Missing `tableManifest` in options given to validator for table validators list',
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

	public function testValidateOptionsReturnsErrorIfOptionsContainsAnInvalidTableManifest()
	{
		$validator = new ValidatorListValidator($this->dependencyManager);


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
				'message' => 'Invalid `tableManifest` option given to validator for table validators list (must be an object)',
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

		$result = $validator->validateOptions(array('tableManifest' => 'invalidTableManifest'));

		$this->assertSame($optionsValidationResult, $result);
	}

	public function testValidateOptionsAcceptsArrayContainingTableManifestAndReturnsResultWithNoErrors()
	{
		$validator = new ValidatorListValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $validator,
				'errors' => null
			))
			->willReturn($validationResult);

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validateOptions(array('tableManifest' => (object)array()));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateThrowsExceptionIfOptionsValidationFails()
	{
		$validator = new ValidatorListValidator($this->dependencyManager);

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
				'message' => 'Missing `tableManifest` in options given to validator for table validators list',
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

	public function testValidateExecutesListStructureValidator()
	{
		$sampleTableManifest = (object)array(
			'fields' => (object)array(),
			'validators' => array()
		);
		$sampleValidators = $sampleTableManifest->validators;

		$validatorValidator = $this->mockValidatorValidator();

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$structureValidator = $this->mockStructureValidator();
		$structureValidationResult = $this->mockValidationResult();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorValidator')
			->will($this->returnValue($validatorValidator));

		$validator = new ValidatorListValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->never())
			->method('buildValidationError');

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleValidators)
			->will($this->returnValue($structureValidationResult));

		$validatorValidator->expects($this->never())
			->method('validate');

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$validator->validate($sampleValidators, array('tableManifest' => $sampleTableManifest));
	}

	public function testValidateReturnsErrorsFromListStructureValidator()
	{
		$sampleValidators = (object)array();

		$validatorValidator = $this->mockValidatorValidator();

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$structureValidator = $this->mockStructureValidator();
		$structureValidationResult = $this->mockValidationResult();
		$structureErrorList = $this->mockValidationErrorList();
		$structureError = $this->mockValidationError();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorValidator')
			->will($this->returnValue($validatorValidator));

		$validator = new ValidatorListValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleValidators)
			->will($this->returnValue($structureValidationResult));

		$validatorValidator->expects($this->never())
			->method('validate');

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$structureValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($structureErrorList));

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Validator list structure is invalid',
				'children' => $structureErrorList
			))
			->willReturn($structureError);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$validationErrorList->expects($this->once())
			->method('push')
			->willReturn($structureError);

		$validator->validate($sampleValidators, array('tableManifest' => (object)array()));
	}

	public function testValidateExecutesValidatorValidators()
	{
		$sampleTableManifest = (object)array(
			'fields' => (object)array(),
			'validators' => array(
				(object)array(
					'rule' => 'comparison.equal',
					'fields' => (object)array('field1', 'field2'),
					'options' => (object)array()
				),
				(object)array(
					'rule' => 'comparison.contains',
					'fields' => (object)array(
						'needle' => 'field1',
						'haystack' => 'field3'
					),
					'options' => (object)array()
				)
			)
		);
		$sampleValidators = $sampleTableManifest->validators;

		$structureValidator = $this->mockStructureValidator();
		$validatorValidator = $this->mockValidatorValidator();

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$structureValidationResult = $this->mockValidationResult();
		$firstValidatorValidationResult = $this->mockValidationResult();
		$secondValidatorValidationResult = $this->mockValidationResult();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorValidator')
			->will($this->returnValue($validatorValidator));

		$validator = new ValidatorListValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleValidators)
			->will($this->returnValue($structureValidationResult));

		$validatorValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(
				array($sampleValidators[0], array('tableManifest' => $sampleTableManifest)),
				array($sampleValidators[1], array('tableManifest' => $sampleTableManifest))
			)
			->willReturnMap(array(
				array(
					$sampleValidators[0],
					array('tableManifest' => $sampleTableManifest),
					$firstValidatorValidationResult
				),
				array(
					$sampleValidators[1],
					array('tableManifest' => $sampleTableManifest),
					$secondValidatorValidationResult
				)
			));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$firstValidatorValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$secondValidatorValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->never())
			->method('buildValidationError');

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$result = $validator->validate($sampleValidators, array('tableManifest' => $sampleTableManifest));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateReturnsValidatorValidatorErrors()
	{
		$sampleTableManifest = (object)array(
			'fields' => (object)array(),
			'validators' => array(
				(object)array(
					'rule' => 'comparison.equal',
					'fields' => (object)array('field1', 'field2'),
					'options' => (object)array()
				),
				(object)array(
					'rule' => 'comparison.contains',
					'fields' => (object)array(
						'needle' => 'field1',
						'haystack' => 'field3'
					),
					'options' => (object)array()
				)
			)
		);
		$sampleValidators = $sampleTableManifest->validators;

		$structureValidator = $this->mockStructureValidator();
		$validatorValidator = $this->mockValidatorValidator();

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$structureValidationResult = $this->mockValidationResult();

		$firstValidatorValidationResult = $this->mockValidationResult();
		$secondValidatorValidationResult = $this->mockValidationResult();
		$firstValidatorErrorList = $this->mockValidationErrorList();
		$secondValidatorErrorList = $this->mockValidationErrorList();
		$firstValidatorError = $this->mockValidationError();
		$secondValidatorError = $this->mockValidationError();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getValidatorValidator')
			->will($this->returnValue($validatorValidator));

		$validator = new ValidatorListValidator($this->dependencyManager);

		$optionsResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);

		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleValidators)
			->will($this->returnValue($structureValidationResult));

		$validatorValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(
				array($sampleValidators[0], array('tableManifest' => $sampleTableManifest)),
				array($sampleValidators[1], array('tableManifest' => $sampleTableManifest))
			)
			->willReturnMap(array(
				array(
					$sampleValidators[0],
					array('tableManifest' => $sampleTableManifest),
					$firstValidatorValidationResult
				),
				array(
					$sampleValidators[1],
					array('tableManifest' => $sampleTableManifest),
					$secondValidatorValidationResult
				)
			));

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$firstValidatorValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$firstValidatorValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($firstValidatorErrorList));

		$secondValidatorValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$secondValidatorValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($secondValidatorErrorList));

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationError')
			->withConsecutive(
				array(
					array(
						'validator' => $validator,
						'message' => 'Validator at index `0` is invalid',
						'children' => $firstValidatorErrorList
					)
				),
				array(
					array(
						'validator' => $validator,
						'message' => 'Validator at index `1` is invalid',
						'children' => $secondValidatorErrorList
					)
				)
			)
			->willReturnOnConsecutiveCalls($firstValidatorError, $secondValidatorError);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->exactly(2))
			->method('push')
			->withConsecutive($firstValidatorError, $secondValidatorError)
			->willReturnSelf();

		$result = $validator->validate($sampleValidators, array('tableManifest' => $sampleTableManifest));

		$this->assertSame($validationResult, $result);
	}

	private function mockStructureValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\ValidatorList\\StructureValidator';

		$structureValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorListStructureValidator')
			->will($this->returnValue($structureValidator));

		return $structureValidator;
	}

	private function mockValidatorValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Validator\\ValidatorValidator';

		$validator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getValidatorValidator')
			->will($this->returnValue($validator));

		return $validator;
	}
}
