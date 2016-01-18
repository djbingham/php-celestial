<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Join\Property;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Join\Property\JoinsValidator;
use Sloth\Module\Validation\Face\ValidationResultInterface;

class JoinsValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$fieldNameValidator = $this->mockFieldNameValidator();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		new JoinsValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayContainingJoinAlias()
	{
		$validator = new JoinsValidator($this->dependencyManager);

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
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateOptionsReturnsErrorIfJoinAliasNotGiven()
	{
		$validator = new JoinsValidator($this->dependencyManager);

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
				'message' => 'Missing `joinAlias` in options given to validator for join property `joins`',
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

		$result = $validator->validateOptions(array());

		$this->assertSame($optionsValidationResult, $result);
	}

	public function testValidateThrowsExceptionIfOptionsValidationFails()
	{
		$sampleJoin = (object)array(
			'joins' => (object)array()
		);

		$validator = new JoinsValidator($this->dependencyManager);

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
				'message' => 'Missing `joinAlias` in options given to validator for join property `joins`',
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
			'Invalid options given to validator for join property `joins`'
		);

		$validator->validate($sampleJoin, array());
	}

	public function testJoinsMustNotBeEmpty()
	{
		$sampleJoin = (object)array(
			'joins' => (object)array()
		);

		$validator = new JoinsValidator($this->dependencyManager);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Join fields are required'
			))
			->willReturn($validationError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testEachJoinParentMustContainTableAndFieldNamesSeparatedByPeriod()
	{
		$sampleJoin = (object)array(
			'joins' => (object)array(
				'this.parentField1' => 'childTable.childField1',
				'parentFieldWithoutTable' => 'childTable.childField2'
			)
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$fieldNameValidator = $this->mockFieldNameValidator();
		$fieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1')
			)
			->willReturnOnConsecutiveCalls(
				$fieldNameValidationResults[0],
				$fieldNameValidationResults[1]
			);

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($fieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Parent `parentFieldWithoutTable` in joins list is not in required format (tableName.FieldName)'
			))
			->willReturn($validationError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testEachJoinChildMustContainTableAndFieldNamesSeparatedByPeriod()
	{
		$sampleJoin = (object)array(
			'joins' => (object)array(
				'this.parentField1' => 'childTable.childField1',
				'this.parentField2' => 'childFieldWithoutTable'
			)
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$fieldNameValidator = $this->mockFieldNameValidator();
		$fieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1')
			)
			->willReturnOnConsecutiveCalls(
				$fieldNameValidationResults[0],
				$fieldNameValidationResults[1]
			);

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($fieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Child `childFieldWithoutTable` in joins list is not in required format (tableName.FieldName)'
			))
			->willReturn($validationError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testSubValidatorsAreCalledForEachValidJoinParentAndChild()
	{
		$sampleJoin = (object)array(
			'joins' => (object)array(
				'this.parentField1' => 'childTable.childField1',
				'this.parentField2' => 'childTable.childField2',
				'this.parentField3' => 'childTable.childField3'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);
		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1'),
				array('parentField2'),
				array('childField2'),
				array('parentField3'),
				array('childField3')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->never())
			->method('buildValidationError');

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->never())
			->method('push');

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}
		foreach ($childFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testParentFieldNameErrorsAreAppendedToErrorsReturnedByValidate()
	{
		$sampleJoin = (object)array(
			'joins' => (object)array(
				'this.parentField1' => 'childTable.childField1',
				'this.parentField2' => 'childTable.childField2',
				'this.parentField3' => 'childTable.childField3'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$parentFieldNameErrorLists = array(
			$this->mockValidationErrorList(),
			$this->mockValidationErrorList(),
			$this->mockValidationErrorList()
		);

		$parentFieldNameErrors = array(
			$this->mockValidationError(),
			$this->mockValidationError(),
			$this->mockValidationError()
		);

		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1'),
				array('parentField2'),
				array('childField2'),
				array('parentField3'),
				array('childField3')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(3))
			->method('buildValidationError')
			->withConsecutive(
				array(
					array(
						'validator' => $validator,
						'message' => 'Join parent field `parentField1` is invalid',
						'children' => $parentFieldNameErrorLists[0]
					)
				),
				array(
					array(
						'validator' => $validator,
						'message' => 'Join parent field `parentField2` is invalid',
						'children' => $parentFieldNameErrorLists[1]
					)
				),
				array(
					array(
						'validator' => $validator,
						'message' => 'Join parent field `parentField3` is invalid',
						'children' => $parentFieldNameErrorLists[2]
					)
				)
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameErrors[0],
				$parentFieldNameErrors[1],
				$parentFieldNameErrors[2]
			);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->exactly(3))
			->method('push')
			->withConsecutive(
				$parentFieldNameErrors[0],
				$parentFieldNameErrors[1],
				$parentFieldNameErrors[2]
			)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $parentFieldIndex => $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(false);

			$result->expects($this->once())
				->method('getErrors')
				->willReturn($parentFieldNameErrorLists[$parentFieldIndex]);
		}

		foreach ($childFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testChildFieldNameErrorsAreAppendedToErrorsReturnedByValidate()
	{
		$sampleJoin = (object)array(
			'joins' => (object)array(
				'this.parentField1' => 'childTable.childField1',
				'this.parentField2' => 'childTable.childField2',
				'this.parentField3' => 'childTable.childField3'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$childFieldNameErrorLists = array(
			$this->mockValidationErrorList(),
			$this->mockValidationErrorList(),
			$this->mockValidationErrorList()
		);

		$childFieldNameErrors = array(
			$this->mockValidationError(),
			$this->mockValidationError(),
			$this->mockValidationError()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1'),
				array('parentField2'),
				array('childField2'),
				array('parentField3'),
				array('childField3')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(3))
			->method('buildValidationError')
			->withConsecutive(
				array(
					array(
						'validator' => $validator,
						'message' => 'Join child field `childField1` is invalid',
						'children' => $childFieldNameErrorLists[0]
					)
				),
				array(
					array(
						'validator' => $validator,
						'message' => 'Join child field `childField2` is invalid',
						'children' => $childFieldNameErrorLists[1]
					)
				),
				array(
					array(
						'validator' => $validator,
						'message' => 'Join child field `childField3` is invalid',
						'children' => $childFieldNameErrorLists[2]
					)
				)
			)
			->willReturnOnConsecutiveCalls(
				$childFieldNameErrors[0],
				$childFieldNameErrors[1],
				$childFieldNameErrors[2]
			);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->exactly(3))
			->method('push')
			->withConsecutive(
				$childFieldNameErrors[0],
				$childFieldNameErrors[1],
				$childFieldNameErrors[2]
			)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		foreach ($childFieldNameValidationResults as $parentFieldIndex => $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(false);

			$result->expects($this->once())
				->method('getErrors')
				->willReturn($childFieldNameErrorLists[$parentFieldIndex]);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testEachJoinParentTableMustBeEitherThisOrAnIntermediateTable()
	{
		$sampleJoin = (object)array(
			'joins' => (object)array(
				'this.parentField1' => 'childTable.childField1',
				'invalidTable.parentField2' => 'childTable.childField2',
				'this.parentField3' => 'childTable.childField3'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);
		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$nameError = $this->mockValidationError();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1'),
				array('parentField2'),
				array('childField2'),
				array('parentField3'),
				array('childField3')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Parent table for join not found: `invalidTable`'
			))
			->willReturn($nameError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($nameError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}
		foreach ($childFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testEachJoinChildMustBeEitherTargetTableOrAnIntermediateTable()
	{
		$sampleJoin = (object)array(
			'joins' => (object)array(
				'this.parentField1' => 'childTable.childField1',
				'this.parentField2' => 'invalidTable.childField2',
				'this.parentField3' => 'childTable.childField3'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);
		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$nameError = $this->mockValidationError();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1'),
				array('parentField2'),
				array('childField2'),
				array('parentField3'),
				array('childField3')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Child table for join not found: `invalidTable`'
			))
			->willReturn($nameError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($nameError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}
		foreach ($childFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testJoinsMayIncludeIntermediateTables()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'intermediateTable1' => 'Intermediate1',
				'intermediateTable2' => 'Intermediate2'
			),
			'joins' => (object)array(
				'this.parentField1' => 'intermediateTable1.childField1',
				'intermediateTable1.parentField2' => 'intermediateTable2.childField2',
				'intermediateTable2.parentField3' => 'childTable.childField3'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);
		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1'),
				array('parentField2'),
				array('childField2'),
				array('parentField3'),
				array('childField3')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->never())
			->method('buildValidationError');

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->never())
			->method('push');

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}
		foreach ($childFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testAllIntermediateTablesMustBeReferencedAsParentAndChildInDifferentJoins()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'intermediateTable1' => 'Intermediate1',
				'intermediateTable2' => 'Intermediate2'
			),
			'joins' => (object)array(
				'this.parentField1' => 'intermediateTable1.childField1',
				'intermediateTable1.parentField2' => 'childTable.childField2',
				'this.parentField3' => 'childTable.childField3'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);
		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationErrors = array(
			$this->mockValidationError(),
			$this->mockValidationError()
		);

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1'),
				array('parentField2'),
				array('childField2'),
				array('parentField3'),
				array('childField3')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationError')
			->withConsecutive(
				array(
					array(
						'message' => 'Intermediate table `intermediateTable2` in via list is not referenced as parent in any joins',
					)
				),
				array(
					array(
						'message' => 'Intermediate table `intermediateTable2` in via list is not referenced as child in any joins',
					)
				)
			)
			->willReturnOnConsecutiveCalls($validationErrors[0], $validationErrors[1]);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->exactly(2))
			->method('push')
			->withConsecutive($validationErrors[0], $validationErrors[1])
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}
		foreach ($childFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testParentAndChildMustNotMatchWithinEachJoin()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'intermediateTable' => 'IntermediateTableName'
			),
			'joins' => (object)array(
				'this.parentField1' => 'intermediateTable.intermediateField1',
				'intermediateTable.intermediateField1' => 'intermediateTable.intermediateField2',
				'intermediateTable.intermediateField2' => 'childTable.childField1'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);
		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('intermediateField1'),
				array('intermediateField1'),
				array('intermediateField2'),
				array('intermediateField2'),
				array('childField1')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Cannot join a table to itself: `intermediateTable`',
			))
			->willReturn($validationError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}
		foreach ($childFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testEachJoinParentMustNotBeTheTargetTable()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'intermediateTable' => 'IntermediateTableName'
			),
			'joins' => (object)array(
				'this.parentField1' => 'childTable.childField1',
				'childTable.childField1' => 'intermediateTable.intermediateField1',
				'intermediateTable.intermediateField2' => 'childTable.childField2'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);
		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('childField1'),
				array('childField1'),
				array('intermediateField1'),
				array('intermediateField2'),
				array('childField2')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Parent table for join not found: `childTable`',
			))
			->willReturn($validationError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}
		foreach ($childFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testEachJoinChildMustNotBeThis()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'intermediateTable' => 'IntermediateTableName'
			),
			'joins' => (object)array(
				'this.parentField1' => 'intermediateTable.intermediateField1',
				'intermediateTable.intermediateField2' => 'this.parentField2',
				'this.parentField2' => 'childTable.childField1'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$parentFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);
		$childFieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('intermediateField1'),
				array('intermediateField2'),
				array('parentField2'),
				array('parentField2'),
				array('childField1')
			)
			->willReturnOnConsecutiveCalls(
				$parentFieldNameValidationResults[0],
				$childFieldNameValidationResults[0],
				$parentFieldNameValidationResults[1],
				$childFieldNameValidationResults[1],
				$parentFieldNameValidationResults[2],
				$childFieldNameValidationResults[2]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Child table for join not found: `this`',
			))
			->willReturn($validationError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($parentFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}
		foreach ($childFieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testThisMustBeIncludedAsParentInAtLeastOneJoin()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'intermediateTable1' => 'Intermediate1',
				'intermediateTable2' => 'Intermediate2'
			),
			'joins' => (object)array(
				'intermediateTable1.intermediateField1' => 'intermediateTable2.intermediateField2',
				'intermediateTable2.intermediateField2' => 'intermediateTable1.intermediateField1',
				'intermediateTable2.intermediateField3' => 'childTable.childField1'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$fieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('intermediateField1'),
				array('intermediateField2'),
				array('intermediateField2'),
				array('intermediateField1'),
				array('intermediateField3'),
				array('childField1')
			)
			->willReturnOnConsecutiveCalls(
				$fieldNameValidationResults[0],
				$fieldNameValidationResults[1],
				$fieldNameValidationResults[2],
				$fieldNameValidationResults[3],
				$fieldNameValidationResults[4],
				$fieldNameValidationResults[5]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Join parent `this` is not referenced as parent in any joins',
			))
			->willReturn($validationError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($fieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testTargetTableMustBeIncludedAsChildInAtLeastOneJoin()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'intermediateTable1' => 'Intermediate1',
				'intermediateTable2' => 'Intermediate2'
			),
			'joins' => (object)array(
				'this.parentField1' => 'intermediateTable1.intermediateField1',
				'intermediateTable1.intermediateField1' => 'intermediateTable2.intermediateField2',
				'intermediateTable2.intermediateField2' => 'intermediateTable1.intermediateField1'
			)
		);

		$fieldNameValidator = $this->mockFieldNameValidator();

		$fieldNameValidationResults = array(
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult(),
			$this->mockValidationResult()
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();
		$validationError = $this->mockValidationError();

		$this->dependencyManager->expects($this->once())
			->method('getFieldNameValidator')
			->will($this->returnValue($fieldNameValidator));

		$validator = new JoinsValidator($this->dependencyManager);

		$fieldNameValidator->expects($this->exactly(6))
			->method('validate')
			->withConsecutive(
				array('parentField1'),
				array('intermediateField1'),
				array('intermediateField1'),
				array('intermediateField2'),
				array('intermediateField2'),
				array('intermediateField1')
			)
			->willReturnOnConsecutiveCalls(
				$fieldNameValidationResults[0],
				$fieldNameValidationResults[1],
				$fieldNameValidationResults[2],
				$fieldNameValidationResults[3],
				$fieldNameValidationResults[4],
				$fieldNameValidationResults[5]
			);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'message' => 'Join target `childTable` is not referenced as child in any joins',
			))
			->willReturn($validationError);

		$optionsValidationResultProperties = array(
			'validator' => $validator,
			'errors' => $optionsErrorList
		);
		$validationResultProperties = array(
			'validator' => $validator,
			'errors' => $validationErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(array($optionsValidationResultProperties), array($validationResultProperties))
			->willReturnMap(array(
				array($optionsValidationResultProperties, $optionsValidationResult),
				array($validationResultProperties, $validationResult)
			));

		$optionsErrorList->expects($this->never())
			->method('push');

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$validationErrorList->expects($this->once())
			->method('push')
			->with($validationError)
			->willReturnSelf();

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		/** @var ValidationResultInterface|\PHPUnit_Framework_MockObject_MockObject $result */
		foreach ($fieldNameValidationResults as $result) {
			$result->expects($this->once())
				->method('isValid')
				->willReturn(true);
		}

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testEachJoinParentFieldMustBeAFieldInTheParentTable()
	{
		$this->markTestSkipped('Not yet validating that fields exist in tables');
	}

	public function testEachJoinChildFieldMustBeAFieldInTheChildTable()
	{
		$this->markTestSkipped('Not yet validating that fields exist in tables');
	}

	private function mockFieldNameValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Field\\Property\\NameValidator';

		$structureValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->any())
			->method('getFieldNameValidator')
			->will($this->returnValue($structureValidator));

		return $structureValidator;
	}
}
