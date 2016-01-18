<?php
namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\JoinList;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\JoinList\JoinListValidator;

class JoinListValidatorTest extends UnitTest
{
	public function testConstructorReadsDependenciesFromDependencyManager()
	{
		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$validationModule = $this->mockValidationModule();

		$this->dependencyManager->expects($this->once())
			->method('getJoinListStructureValidator')
			->willReturn($structureValidator);

		$this->dependencyManager->expects($this->once())
			->method('getJoinListAliasValidator')
			->willReturn($aliasValidator);

		$this->dependencyManager->expects($this->once())
			->method('getValidationModule')
			->willReturn($validationModule);

		new JoinListValidator($this->dependencyManager);
	}

	public function testValidateOptionsAcceptsArrayAndReturnsResultWithNoErrors()
	{
		$joinValidator = new JoinListValidator($this->dependencyManager);

		$validationResult = $this->mockValidationResult();

		$this->validationModule->expects($this->once())
			->method('buildValidationResult')
			->with(array('validator' => $joinValidator))
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

	public function testValidateExecutesListStructureValidator()
	{
		$sampleJoinList = (object)array();

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$joinValidator = $this->mockJoinValidator();

		$structureValidationResult = $this->mockValidationResult();

		$this->dependencyManager->expects($this->once())
			->method('getJoinListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinValidator')
			->will($this->returnValue($joinValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleJoinList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->never())
			->method('validate');

		$joinValidator->expects($this->never())
			->method('validate');

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$validator = new JoinListValidator($this->dependencyManager);

		$validator->validate($sampleJoinList);
	}

	public function testValidateReturnsErrorsFromListStructureValidator()
	{
		$sampleJoinList = (object)array();

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$joinValidator = $this->mockJoinValidator();

		$structureValidationResult = $this->mockValidationResult();
		$structureErrorList = $this->mockValidationErrorList();
		$structureError = $this->mockValidationError();

		$errorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getJoinListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinValidator')
			->will($this->returnValue($joinValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleJoinList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->never())
			->method('validate');

		$joinValidator->expects($this->never())
			->method('validate');

		$structureValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$structureValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($structureErrorList));

		$validator = new JoinListValidator($this->dependencyManager);

		$this->validationModule->expects($this->once())
			->method('buildValidationError')
			->with(array(
				'validator' => $validator,
				'message' => 'Join list structure is invalid',
				'children' => $structureErrorList
			))
			->willReturn($structureError);

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$errorList->expects($this->once())
			->method('push')
			->willReturn($structureError);

		$validator->validate($sampleJoinList);
	}

	public function testValidateExecutesJoinAndAliasValidators()
	{
		$sampleJoinList = (object)array(
			'firstJoin' => array(
				'autoIncrement' => true,
				'isUnique' => true,
				'name' => 'firstJoinName',
				'type' => 'number(11)',
				'validators' => array()
			),
			'secondJoin' => array(
				'autoIncrement' => false,
				'isUnique' => false,
				'name' => 'secondJoinName',
				'type' => 'text(20)',
				'validators' => array()
			)
		);

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$joinValidator = $this->mockJoinValidator();

		$structureValidationResult = $this->mockValidationResult();
		$firstAliasValidationResult = $this->mockValidationResult();
		$secondAliasValidationResult = $this->mockValidationResult();
		$firstJoinValidationResult = $this->mockValidationResult();
		$secondJoinValidationResult = $this->mockValidationResult();

		$this->dependencyManager->expects($this->once())
			->method('getJoinListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinValidator')
			->will($this->returnValue($joinValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleJoinList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array('firstJoin', array(), $firstAliasValidationResult),
				array('secondJoin', array(), $secondAliasValidationResult)
			));

		$joinValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array($sampleJoinList->firstJoin, array('joinAlias' => 'firstJoin'), $firstJoinValidationResult),
				array($sampleJoinList->secondJoin, array('joinAlias' => 'secondJoin'), $secondJoinValidationResult)
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

		$firstJoinValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$secondJoinValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$validator = new JoinListValidator($this->dependencyManager);

		$validator->validate($sampleJoinList);
	}

	public function testValidateReturnsJoinValidatorErrors()
	{
		$sampleJoinList = (object)array(
			'firstJoin' => array(
				'autoIncrement' => true,
				'isUnique' => true,
				'name' => 'firstJoinName',
				'type' => 'number(11)',
				'validators' => array()
			),
			'secondJoin' => array(
				'autoIncrement' => false,
				'isUnique' => false,
				'name' => 'secondJoinName',
				'type' => 'text(20)',
				'validators' => array()
			)
		);

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$joinValidator = $this->mockJoinValidator();

		$structureValidationResult = $this->mockValidationResult();
		$firstAliasValidationResult = $this->mockValidationResult();
		$secondAliasValidationResult = $this->mockValidationResult();
		$firstJoinValidationResult = $this->mockValidationResult();
		$secondJoinValidationResult = $this->mockValidationResult();

		$firstJoinErrorList = $this->mockValidationErrorList();
		$secondJoinErrorList = $this->mockValidationErrorList();
		$firstJoinError = $this->mockValidationError();
		$secondJoinError = $this->mockValidationError();

		$errorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getJoinListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinValidator')
			->will($this->returnValue($joinValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleJoinList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array('firstJoin', array(), $firstAliasValidationResult),
				array('secondJoin', array(), $secondAliasValidationResult)
			));

		$joinValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array($sampleJoinList->firstJoin, array('joinAlias' => 'firstJoin'), $firstJoinValidationResult),
				array($sampleJoinList->secondJoin, array('joinAlias' => 'secondJoin'), $secondJoinValidationResult)
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

		$firstJoinValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$firstJoinValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($firstJoinErrorList));

		$secondJoinValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(false));

		$secondJoinValidationResult->expects($this->once())
			->method('getErrors')
			->will($this->returnValue($secondJoinErrorList));

		$validator = new JoinListValidator($this->dependencyManager);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationError')
			->withConsecutive(
				array(
					array(
						'validator' => $validator,
						'message' => 'Join with alias `firstJoin` is invalid',
						'children' => $firstJoinErrorList
					)
				),
				array(
					array(
						'validator' => $validator,
						'message' => 'Join with alias `secondJoin` is invalid',
						'children' => $secondJoinErrorList
					)
				)
			)
			->willReturnOnConsecutiveCalls($firstJoinError, $secondJoinError);

		$this->validationModule->expects($this->once())
			->method('buildValidationErrorList')
			->willReturn($errorList);

		$errorList->expects($this->exactly(2))
			->method('push')
			->withConsecutive($firstJoinError, $secondJoinError)
			->willReturnSelf();

		$validator->validate($sampleJoinList);
	}

	public function testValidateReturnsAliasValidatorErrors()
	{
		$sampleJoinList = (object)array(
			'firstJoin' => array(
				'autoIncrement' => true,
				'isUnique' => true,
				'name' => 'firstJoinName',
				'type' => 'number(11)',
				'validators' => array()
			),
			'secondJoin' => array(
				'autoIncrement' => false,
				'isUnique' => false,
				'name' => 'secondJoinName',
				'type' => 'text(20)',
				'validators' => array()
			)
		);

		$structureValidator = $this->mockStructureValidator();
		$aliasValidator = $this->mockAliasValidator();
		$joinValidator = $this->mockJoinValidator();

		$structureValidationResult = $this->mockValidationResult();
		$firstAliasValidationResult = $this->mockValidationResult();
		$secondAliasValidationResult = $this->mockValidationResult();
		$firstJoinValidationResult = $this->mockValidationResult();
		$secondJoinValidationResult = $this->mockValidationResult();

		$firstAliasErrorList = $this->mockValidationErrorList();
		$secondAliasErrorList = $this->mockValidationErrorList();
		$firstAliasError = $this->mockValidationError();
		$secondAliasError = $this->mockValidationError();

		$errorList = $this->mockValidationErrorList();

		$this->dependencyManager->expects($this->once())
			->method('getJoinListStructureValidator')
			->will($this->returnValue($structureValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinListAliasValidator')
			->will($this->returnValue($aliasValidator));

		$this->dependencyManager->expects($this->once())
			->method('getJoinValidator')
			->will($this->returnValue($joinValidator));

		$structureValidator->expects($this->once())
			->method('validate')
			->with($sampleJoinList)
			->will($this->returnValue($structureValidationResult));

		$aliasValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array('firstJoin', array(), $firstAliasValidationResult),
				array('secondJoin', array(), $secondAliasValidationResult)
			));

		$joinValidator->expects($this->exactly(2))
			->method('validate')
			->willReturnMap(array(
				array($sampleJoinList->firstJoin, array('joinAlias' => 'firstJoin'), $firstJoinValidationResult),
				array($sampleJoinList->secondJoin, array('joinAlias' => 'secondJoin'), $secondJoinValidationResult)
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

		$firstJoinValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$secondJoinValidationResult->expects($this->once())
			->method('isValid')
			->will($this->returnValue(true));

		$validator = new JoinListValidator($this->dependencyManager);

		$this->validationModule->expects($this->exactly(2))
			->method('buildValidationError')
			->withConsecutive(
				array(
					array(
						'validator' => $validator,
						'message' => 'Join alias `firstJoin` is invalid',
						'children' => $firstAliasErrorList
					)
				),
				array(
					array(
						'validator' => $validator,
						'message' => 'Join alias `secondJoin` is invalid',
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

		$validator->validate($sampleJoinList);
	}

	private function mockStructureValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\JoinList\\StructureValidator';

		$structureValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getJoinListStructureValidator')
			->will($this->returnValue($structureValidator));

		return $structureValidator;
	}

	private function mockAliasValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\JoinList\\AliasValidator';

		$structureValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getJoinListAliasValidator')
			->will($this->returnValue($structureValidator));

		return $structureValidator;
	}

	private function mockJoinValidator()
	{
		$className = 'Sloth\\Module\\Data\\TableValidation\\Validator\\Join\\JoinValidator';

		$joinValidator = $this->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();

		$this->dependencyManager->expects($this->once())
			->method('getJoinValidator')
			->will($this->returnValue($joinValidator));

		return $joinValidator;
	}
}
