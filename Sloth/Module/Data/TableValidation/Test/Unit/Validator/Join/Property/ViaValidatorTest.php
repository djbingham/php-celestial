<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Join\Property;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Join\Property\ViaValidator;

class ViaValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$this->mockViaPropertyValidator('tableAlias');
		$this->mockViaPropertyValidator('tableName');

		$this->dependencyManager->expects($this->once())
			->method('getValidationModule');

		$this->dependencyManager->expects($this->once())
			->method('getJoinViaTableAliasValidator');

		$this->dependencyManager->expects($this->once())
			->method('getJoinViaTableNameValidator');

		new ViaValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayContainingJoinAlias()
	{
		$validator = new ViaValidator($this->dependencyManager);

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
			'joinAlias' => 'myJoinAlias'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateOptionsReturnsErrorIfJoinAliasNotGiven()
	{
		$validator = new ViaValidator($this->dependencyManager);

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
				'message' => 'Missing `joinAlias` in options given to validator for join property `via`',
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
			'type' => 'oneToOne',
			'table' => 'tableName',
			'via' => 'intermediateTable',
			'joins' => (object)array(),
			'onInsert' => 'insert',
			'onUpdate' => 'update',
			'onDelete' => 'delete'
		);

		$validator = new ViaValidator($this->dependencyManager);

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
				'message' => 'Missing `joinAlias` in options given to validator for join property `via`',
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

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(false);

		$optionsValidationResult->expects($this->never())
			->method('pushError');

		$optionsValidationResult->expects($this->never())
			->method('pushErrors');

		$this->setExpectedException(
			'Sloth\Exception\InvalidArgumentException',
			'Invalid options given to validator for join property `via`'
		);

		$validator->validate($sampleJoin, array());
	}

	public function testViaValueMayBeAnEmptyObject()
	{
		$sampleJoin = (object)array(
			'via' => (object)array()
		);

		$validator = new ViaValidator($this->dependencyManager);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

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

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testSubValidatorsAreCalledForEachTableAliasAndName()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'firstTableAlias' => 'firstTableName',
				'secondTableAlias' => 'secondTableName'
			)
		);

		$tableAliasValidator = $this->mockViaPropertyValidator('tableAlias');
		$tableNameValidator = $this->mockViaPropertyValidator('tableName');

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$firstTableAliasResult = $this->mockValidationResult();
		$secondTableAliasResult = $this->mockValidationResult();
		$firstTableNameResult = $this->mockValidationResult();
		$secondTableNameResult = $this->mockValidationResult();
		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$validator = new ViaValidator($this->dependencyManager);

		$tableAliasValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(array('firstTableAlias'), array('secondTableAlias'))
			->willReturnOnConsecutiveCalls($firstTableAliasResult, $secondTableAliasResult);

		$tableNameValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(array('firstTableName'), array('secondTableName'))
			->willReturnOnConsecutiveCalls($firstTableNameResult, $secondTableNameResult);

		$firstTableAliasResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$secondTableAliasResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$firstTableNameResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$secondTableNameResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

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

		$result = $validator->validate($sampleJoin, array(
			'joinAlias' => 'childTable'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testTableAliasErrorsAreAppendedToErrorsReturnedByValidate()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'firstTableAlias' => 'firstTableName',
				'secondTableAlias' => 'secondTableName'
			)
		);

		$tableAliasValidator = $this->mockViaPropertyValidator('tableAlias');
		$tableNameValidator = $this->mockViaPropertyValidator('tableName');

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$firstTableAliasResult = $this->mockValidationResult();
		$secondTableAliasResult = $this->mockValidationResult();

		$firstTableAliasErrorList = $this->mockValidationErrorList();
		$secondTableAliasErrorList = $this->mockValidationErrorList();

		$firstTableAliasError = $this->mockValidationError();
		$secondTableAliasError = $this->mockValidationError();

		$firstTableNameResult = $this->mockValidationResult();
		$secondTableNameResult = $this->mockValidationResult();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$validator = new ViaValidator($this->dependencyManager);

		$tableAliasValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(array('firstTableAlias'), array('secondTableAlias'))
			->willReturnOnConsecutiveCalls($firstTableAliasResult, $secondTableAliasResult);

		$tableNameValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(array('firstTableName'), array('secondTableName'))
			->willReturnOnConsecutiveCalls($firstTableNameResult, $secondTableNameResult);

		$firstTableAliasResult->expects($this->once())
			->method('isValid')
			->willReturn(false);

		$secondTableAliasResult->expects($this->once())
			->method('isValid')
			->willReturn(false);

		$firstTableAliasResult->expects($this->once())
			->method('getErrors')
			->willReturn($firstTableAliasErrorList);

		$secondTableAliasResult->expects($this->once())
			->method('getErrors')
			->willReturn($secondTableAliasErrorList);

		$firstTableNameResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$secondTableNameResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$firstTableAliasErrorProperties = array(
			'validator' => $validator,
			'message' => 'Table alias `firstTableAlias` in join property `via` is invalid',
			'children' => $firstTableAliasErrorList
		);

		$secondTableAliasErrorProperties = array(
			'validator' => $validator,
			'message' => 'Table alias `secondTableAlias` in join property `via` is invalid',
			'children' => $secondTableAliasErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationError')
			->withConsecutive(array($firstTableAliasErrorProperties), array($secondTableAliasErrorProperties))
			->willReturnOnConsecutiveCalls($firstTableAliasError, $secondTableAliasError);

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
			->withConsecutive(array($firstTableAliasError), array($secondTableAliasError))
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

	public function testTableNameErrorsAreAppendedToErrorsReturnedByValidate()
	{
		$sampleJoin = (object)array(
			'via' => (object)array(
				'firstTableAlias' => 'firstTableName',
				'secondTableAlias' => 'secondTableName'
			)
		);

		$tableAliasValidator = $this->mockViaPropertyValidator('tableAlias');
		$tableNameValidator = $this->mockViaPropertyValidator('tableName');

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$firstTableAliasResult = $this->mockValidationResult();
		$secondTableAliasResult = $this->mockValidationResult();

		$firstTableNameResult = $this->mockValidationResult();
		$secondTableNameResult = $this->mockValidationResult();

		$firstTableNameErrorList = $this->mockValidationErrorList();
		$secondTableNameErrorList = $this->mockValidationErrorList();

		$firstTableNameError = $this->mockValidationError();
		$secondTableNameError = $this->mockValidationError();

		$validationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$validator = new ViaValidator($this->dependencyManager);

		$tableAliasValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(array('firstTableAlias'), array('secondTableAlias'))
			->willReturnOnConsecutiveCalls($firstTableAliasResult, $secondTableAliasResult);

		$tableNameValidator->expects($this->exactly(2))
			->method('validate')
			->withConsecutive(array('firstTableName'), array('secondTableName'))
			->willReturnOnConsecutiveCalls($firstTableNameResult, $secondTableNameResult);

		$firstTableAliasResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$secondTableAliasResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$firstTableNameResult->expects($this->once())
			->method('isValid')
			->willReturn(false);

		$secondTableNameResult->expects($this->once())
			->method('isValid')
			->willReturn(false);

		$firstTableNameResult->expects($this->once())
			->method('getErrors')
			->willReturn($firstTableNameErrorList);

		$secondTableNameResult->expects($this->once())
			->method('getErrors')
			->willReturn($secondTableNameErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$firstTableNameErrorProperties = array(
			'validator' => $validator,
			'message' => 'Table name `firstTableName` in join property `via` is invalid',
			'children' => $firstTableNameErrorList
		);

		$secondTableNameErrorProperties = array(
			'validator' => $validator,
			'message' => 'Table name `secondTableName` in join property `via` is invalid',
			'children' => $secondTableNameErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationError')
			->withConsecutive(array($firstTableNameErrorProperties), array($secondTableNameErrorProperties))
			->willReturnOnConsecutiveCalls($firstTableNameError, $secondTableNameError);

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
			->withConsecutive(array($firstTableNameError), array($secondTableNameError))
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

	private function mockViaPropertyValidator($propertyName)
	{
		$className = ucfirst($propertyName) . 'Validator';
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Join\\Property\\Via\\' . $className;

		$methodName = 'getJoinVia' . ucfirst($propertyName) . 'Validator';

		$propertyValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->any())
			->method($methodName)
			->will($this->returnValue($propertyValidator));

		return $propertyValidator;
	}

}
