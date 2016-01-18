<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Join;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Join\JoinValidator;

class JoinValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$structureValidator = $this->mockStructureValidator();
		$propertyValidators = array(
			'type' => $this->mockPropertyValidator('Type'),
			'table' => $this->mockPropertyValidator('Table'),
			'joins' => $this->mockPropertyValidator('Joins'),
			'via' => $this->mockPropertyValidator('Via'),
			'onInsert' => $this->mockPropertyValidator('OnInsert'),
			'onUpdate' => $this->mockPropertyValidator('OnUpdate'),
			'onDelete' => $this->mockPropertyValidator('OnDelete')
		);

		$this->dependencyManager->expects($this->once())
			->method('getJoinStructureValidator')
			->will($this->returnValue($structureValidator));

		foreach ($propertyValidators as $propertyName => $propertyValidator) {
			$methodName = 'getJoin' . ucfirst($propertyName) . 'Validator';

			$this->dependencyManager->expects($this->once())
				->method($methodName)
				->will($this->returnValue($propertyValidator));
		}

		new JoinValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayContainingJoinAlias()
	{
		$joinValidator = new JoinValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $joinValidator,
				'errors' => $errorList
			))
			->willReturn($validationResult);

		$validationResult->expects($this->never())
			->method('pushError');

		$validationResult->expects($this->never())
			->method('pushErrors');

		$result = $joinValidator->validateOptions(array(
			'joinAlias' => 'myJoinAlias'
		));

		$this->assertSame($validationResult, $result);
	}

	public function testValidateOptionsReturnsErrorIfJoinAliasNotGiven()
	{
		$joinValidator = new JoinValidator($this->dependencyManager);

		$optionsValidationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();
		$error = $this->mockValidationError();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $joinValidator,
				'message' => 'Missing `joinAlias` option for join validator',
				'children' => null
			))
			->willReturn($error);

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $joinValidator,
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

		$result = $joinValidator->validateOptions(array());

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

		$joinValidator = new JoinValidator($this->dependencyManager);

		$optionsValidationResult = $this->mockValidationResult();
		$errorList = $this->mockValidationErrorList();
		$error = $this->mockValidationError();

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $joinValidator,
				'message' => 'Missing `joinAlias` option for join validator',
				'children' => null
			))
			->willReturn($error);

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array(
				'validator' => $joinValidator,
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

		$this->setExpectedException('Sloth\Exception\InvalidArgumentException', 'Invalid options given to join validator');

		$joinValidator->validate($sampleJoin, array());
	}

	public function testValidateExecutesAllSubValidators()
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

		$validationOptions = array(
			'joinAlias' => 'myJoinAlias'
		);

		$structureValidator = $this->mockStructureValidator();
		$propertyValidators = array(
			'type' => $this->mockPropertyValidator('Type'),
			'table' => $this->mockPropertyValidator('Table'),
			'via' => $this->mockPropertyValidator('Via'),
			'joins' => $this->mockPropertyValidator('Joins'),
			'onInsert' => $this->mockPropertyValidator('OnInsert'),
			'onUpdate' => $this->mockPropertyValidator('OnUpdate'),
			'onDelete' => $this->mockPropertyValidator('OnDelete')
		);

		$structureValidationResult = $this->mockValidationResult();
		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$overallValidationResult = $this->mockValidationResult();
		$validationErrorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getJoinStructureValidator')
			->will($this->returnValue($structureValidator));

		$validator = new JoinValidator($this->dependencyManager);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $validationErrorList);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(
				array(
					array(
						'validator' => $validator,
						'errors' => $optionsErrorList
					)
				),
				array(
					array(
						'validator' => $validator,
						'errors' => $validationErrorList
					)
				)
			)
			->willReturnOnConsecutiveCalls($optionsValidationResult, $overallValidationResult);

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleJoin)
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
				->with($sampleJoin, $validationOptions)
				->will($this->returnValue($propertyValidationResult));
		}

		$validator->validate($sampleJoin, $validationOptions);
	}

	public function testValidateReturnsErrorsFromStructureValidator()
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

		$validationOptions = array(
			'joinAlias' => 'myJoinAlias'
		);

		$structureValidator = $this->mockStructureValidator();
		$propertyValidators = array(
			'type' => $this->mockPropertyValidator('Type'),
			'table' => $this->mockPropertyValidator('Table'),
			'joins' => $this->mockPropertyValidator('Joins'),
			'via' => $this->mockPropertyValidator('Via'),
			'onInsert' => $this->mockPropertyValidator('OnInsert'),
			'onUpdate' => $this->mockPropertyValidator('OnUpdate'),
			'onDelete' => $this->mockPropertyValidator('OnDelete')
		);

		$structureValidationResult = $this->mockValidationResult();
		$structureErrorList = $this->mockValidationErrorList();

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();
		$joinValidationResult = $this->mockValidationResult();
		$joinErrorList = $this->mockValidationErrorList();
		$joinError = $this->mockValidationError();

		$this->dependencyManager->expects($this->once())
			->method('getJoinStructureValidator')
			->will($this->returnValue($structureValidator));

		$joinValidator = new JoinValidator($this->dependencyManager);

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleJoin)
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
				->with($sampleJoin)
				->will($this->returnValue($propertyValidationResult));
		}

		$errorProperties = array(
			'validator' => $joinValidator,
			'message' => 'Join structure is invalid',
			'children' => $structureErrorList
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $joinErrorList);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with($errorProperties)
			->will($this->returnValue($joinError));

		$joinErrorList->expects($this->once())
			->method('push')
			->withConsecutive($joinError)
			->will($this->returnSelf());

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(
				array(
					array(
						'validator' => $joinValidator,
						'errors' => $optionsErrorList
					)
				),
				array(
					array(
						'validator' => $joinValidator,
						'errors' => $joinErrorList
					)
				)
			)
			->willReturnOnConsecutiveCalls($optionsValidationResult, $joinValidationResult);

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$output = $joinValidator->validate($sampleJoin, $validationOptions);

		$this->assertSame($joinValidationResult, $output);
	}

	public function testValidateReturnsErrorsFromPropertyValidators()
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

		$validationOptions = array(
			'joinAlias' => 'myJoinAlias'
		);

		$structureValidator = $this->mockStructureValidator();
		$propertyValidators = array(
			'type' => $this->mockPropertyValidator('Type'),
			'table' => $this->mockPropertyValidator('Table'),
			'joins' => $this->mockPropertyValidator('Joins'),
			'via' => $this->mockPropertyValidator('Via'),
			'onInsert' => $this->mockPropertyValidator('OnInsert'),
			'onUpdate' => $this->mockPropertyValidator('OnUpdate'),
			'onDelete' => $this->mockPropertyValidator('OnDelete')
		);

		$optionsValidationResult = $this->mockValidationResult();
		$optionsErrorList = $this->mockValidationErrorList();

		$structureValidationResult = $this->mockValidationResult();
		$propertyErrors = array(
			'type' => $this->mockValidationErrorList(),
			'table' => $this->mockValidationErrorList(),
			'via' => $this->mockValidationErrorList(),
			'joins' => $this->mockValidationErrorList(),
			'onInsert' => $this->mockValidationErrorList(),
			'onUpdate' => $this->mockValidationErrorList(),
			'onDelete' => $this->mockValidationErrorList()
		);

		$joinValidationResult = $this->mockValidationResult();
		$joinErrorList = $this->mockValidationErrorList();
		$joinErrors = array(
			'type' => $this->mockValidationError(),
			'table' => $this->mockValidationError(),
			'via' => $this->mockValidationError(),
			'joins' => $this->mockValidationError(),
			'onInsert' => $this->mockValidationError(),
			'onUpdate' => $this->mockValidationError(),
			'onDelete' => $this->mockValidationError()
		);

		$joinValidator = new JoinValidator($this->dependencyManager);

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleJoin)
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
				->with($sampleJoin)
				->will($this->returnValue($propertyValidationResult));
		}

		$errorProperties = array(
			'type' => array(
				'validator' => $joinValidator,
				'message' => 'Value of `type` property is invalid',
				'children' => $propertyErrors['type']
			),
			'table' => array(
				'validator' => $joinValidator,
				'message' => 'Value of `table` property is invalid',
				'children' => $propertyErrors['table']
			),
			'via' => array(
				'validator' => $joinValidator,
				'message' => 'Value of `via` property is invalid',
				'children' => $propertyErrors['via']
			),
			'joins' => array(
				'validator' => $joinValidator,
				'message' => 'Value of `joins` property is invalid',
				'children' => $propertyErrors['joins']
			),
			'onInsert' => array(
				'validator' => $joinValidator,
				'message' => 'Value of `onInsert` property is invalid',
				'children' => $propertyErrors['onInsert']
			),
			'onUpdate' => array(
				'validator' => $joinValidator,
				'message' => 'Value of `onUpdate` property is invalid',
				'children' => $propertyErrors['onUpdate']
			),
			'onDelete' => array(
				'validator' => $joinValidator,
				'message' => 'Value of `onDelete` property is invalid',
				'children' => $propertyErrors['onDelete']
			)
		);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationErrorList')
			->willReturnOnConsecutiveCalls($optionsErrorList, $joinErrorList);

		$this->validationModule->expects($this->exactly(7))
			->method('buildValidationError')
			->will($this->returnValueMap(array(
				array($errorProperties['type'], $joinErrors['type']),
				array($errorProperties['table'], $joinErrors['table']),
				array($errorProperties['via'], $joinErrors['via']),
				array($errorProperties['joins'], $joinErrors['joins']),
				array($errorProperties['onInsert'], $joinErrors['onInsert']),
				array($errorProperties['onUpdate'], $joinErrors['onUpdate']),
				array($errorProperties['onDelete'], $joinErrors['onDelete'])
			)));

		$joinErrorList->expects($this->exactly(7))
			->method('push')
			->withConsecutive(
				$joinErrors['type'],
				$joinErrors['table'],
				$joinErrors['via'],
				$joinErrors['joins'],
				$joinErrors['onInsert'],
				$joinErrors['onUpdate'],
				$joinErrors['onDelete']
			)
			->will($this->returnSelf());

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationResult')
			->withConsecutive(
				array(
					array(
						'validator' => $joinValidator,
						'errors' => $optionsErrorList
					)
				),
				array(
					array(
						'validator' => $joinValidator,
						'errors' => $joinErrorList
					)
				)
			)
			->willReturnOnConsecutiveCalls($optionsValidationResult, $joinValidationResult);

		$optionsValidationResult->expects($this->once())
			->method('isValid')
			->willReturn(true);

		$output = $joinValidator->validate($sampleJoin, $validationOptions);

		$this->assertSame($joinValidationResult, $output);
	}

	private function mockPropertyValidator($propertyName)
	{
		$className = ucfirst($propertyName) . 'Validator';
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Join\\Property\\' . $className;

		$methodName = 'getJoin' . ucfirst($propertyName) . 'Validator';

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
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Join\\StructureValidator';

		$structureValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getJoinStructureValidator')
			->will($this->returnValue($structureValidator));

		return $structureValidator;
	}
}
