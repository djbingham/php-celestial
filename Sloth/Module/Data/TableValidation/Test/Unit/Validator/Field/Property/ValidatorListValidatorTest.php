<?php

namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Field\Property;

use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Field\Property\ValidatorListValidator;
use Sloth\Module\Validation\ValidationModule;

class ValidatorListValidatorTest extends UnitTest
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
            ->will($this->returnValue($this->validationModule));
    }

    public function testValidateOptionsReturnsValidationResultWithoutErrors()
    {
        $structureValidator = new ValidatorListValidator($this->dependencyManager);

        $validationResult = $this->mockValidationResult();

        $this->validationModule->expects($this->once())
            ->method('buildValidationResult')
            ->with(array('validator' => $structureValidator))
            ->will($this->returnValue($validationResult));

        $validationResult->expects($this->never())
            ->method('pushError');

        $validationResult->expects($this->never())
            ->method('pushErrors');

        $result = $structureValidator->validateOptions(array());

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorListMustNotBePlainObject()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForSingleError(
            $validatorListValidator,
            $validationResult,
            'Validator list must be an instance of ValidatorListInterface'
        );

        $result = $validatorListValidator->validate(new \stdClass());

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorListMustNotBeString()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForSingleError(
            $validatorListValidator,
            $validationResult,
            'Validator list must be an instance of ValidatorListInterface'
        );

        $result = $validatorListValidator->validate('Some string');

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorListMustNotBeNumber()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForSingleError(
            $validatorListValidator,
            $validationResult,
            'Validator list must be an instance of ValidatorListInterface'
        );

        $result = $validatorListValidator->validate(27);

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorListMustNotBeBoolean()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForSingleError(
            $validatorListValidator,
            $validationResult,
            'Validator list must be an instance of ValidatorListInterface'
        );

        $result = $validatorListValidator->validate(true);

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorListMayBeAnEmptyArray()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForNoErrors($validatorListValidator, $validationResult);

        $result = $validatorListValidator->validate(array());

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorNameMustMatchARegisteredValidator()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForSingleError(
            $validatorListValidator,
            $validationResult,
            'Invalid validator declared. No validator named `NotValidator` exists.'
        );

        $result = $validatorListValidator->validate(array(
            'NotValidator' => array()
        ));

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorNameMayBeARegisteredValidatorAndItsOptionsAreValidated()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();
        $mockValidator = $this->mockValidator();
        $optionsValidationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForNoErrors($validatorListValidator, $validationResult);

        $this->validationModule->expects($this->once())
            ->method('validatorExists')
            ->with('number.integer')
            ->willReturn(true);

        $this->validationModule->expects($this->once())
            ->method('getValidator')
            ->with('number.integer')
            ->willReturn($mockValidator);

        $mockValidator->expects($this->once())
            ->method('validateOptions')
            ->with(array('compareTo' => true))
            ->willReturn($optionsValidationResult);

        $optionsValidationResult->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $result = $validatorListValidator->validate(array(
            'number.integer' => true
        ));

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorOptionsObjectIsConvertedToArrayThenValidated()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();
        $mockValidator = $this->mockValidator();
        $optionsValidationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForNoErrors($validatorListValidator, $validationResult);

        $this->validationModule->expects($this->once())
            ->method('validatorExists')
            ->with('number.integer')
            ->willReturn(true);

        $this->validationModule->expects($this->once())
            ->method('getValidator')
            ->with('number.integer')
            ->willReturn($mockValidator);

        $mockValidator->expects($this->once())
            ->method('validateOptions')
            ->with(array('validatorOption' => 'optionValue'))
            ->willReturn($optionsValidationResult);

        $optionsValidationResult->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $result = $validatorListValidator->validate(array(
            'number.integer' => (object)array('validatorOption' => 'optionValue')
        ));

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorOptionsStringIsPutIntoArrayThenValidated()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();
        $mockValidator = $this->mockValidator();
        $optionsValidationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForNoErrors($validatorListValidator, $validationResult);

        $this->validationModule->expects($this->once())
            ->method('validatorExists')
            ->with('number.integer')
            ->willReturn(true);

        $this->validationModule->expects($this->once())
            ->method('getValidator')
            ->with('number.integer')
            ->willReturn($mockValidator);

        $mockValidator->expects($this->once())
            ->method('validateOptions')
            ->with(array('compareTo' => true))
            ->willReturn($optionsValidationResult);

        $optionsValidationResult->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $result = $validatorListValidator->validate(array(
            'number.integer' => true
        ));

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorOptionsErrorsAreMergedIntoReturnedErrors()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();
        $mockValidator = $this->mockValidator();
        $optionsValidationResult = $this->mockValidationResult();
        $validatorOptionsErrors = $this->mockValidationErrorList();

        $this->setupMockExpectationsForSingleError(
            $validatorListValidator,
            $validationResult,
            'Invalid options declared for validator `number.integer`',
            $validatorOptionsErrors
        );

        $this->validationModule->expects($this->once())
            ->method('validatorExists')
            ->with('number.integer')
            ->willReturn(true);

        $this->validationModule->expects($this->once())
            ->method('getValidator')
            ->with('number.integer')
            ->willReturn($mockValidator);

        $mockValidator->expects($this->once())
            ->method('validateOptions')
            ->with(array('compareTo' => 'invalidOptionValue'))
            ->willReturn($optionsValidationResult);

        $optionsValidationResult->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $optionsValidationResult->expects($this->once())
            ->method('getErrors')
            ->willReturn($validatorOptionsErrors);

        $result = $validatorListValidator->validate(array(
            'number.integer' => 'invalidOptionValue'
        ));

        $this->assertSame($validationResult, $result);
    }

    private function mockValidator()
    {
        return $this->getMockBuilder('Sloth\\Module\\Validation\\Face\\ValidatorInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function setupMockExpectationsForSingleError(
        ValidatorListValidator $validator,
        \PHPUnit_Framework_MockObject_MockObject $result,
        $errorMessage,
        $childErrorList = null
    ) {
        $errorList = $this->mockValidationErrorList();
        $error = $this->mockValidationError();

        $this->validationModule->expects($this->once())
            ->method('buildValidationErrorList')
            ->will($this->returnValue($errorList));

        $this->validationModule->expects($this->once())
            ->method('buildValidationError')
            ->with(array(
                'validator' => $validator,
                'message' => $errorMessage,
                'children' => $childErrorList
            ))
            ->will($this->returnValue($error));

        $errorList->expects($this->once())
            ->method('push')
            ->with($error)
            ->will($this->returnSelf());

        // result.pushError should not be called, since we pushed directly onto errorList
        $result->expects($this->never())
            ->method('pushError');

        // result.pushErrorList should not be called, since we pushed directly onto errorList
        $result->expects($this->never())
            ->method('pushErrors');

        $this->validationModule->expects($this->once())
            ->method('buildValidationResult')
            ->with(array(
                'validator' => $validator,
                'errors' => $errorList
            ))
            ->will($this->returnValue($result));

        return $result;
    }

    private function setupMockExpectationsForNoErrors(
        ValidatorListValidator $validator,
        \PHPUnit_Framework_MockObject_MockObject $result
    ) {
        $errorList = $this->mockValidationErrorList();

        $this->validationModule->expects($this->once())
            ->method('buildValidationErrorList')
            ->will($this->returnValue($errorList));

        $this->validationModule->expects($this->never())
            ->method('buildValidationError');

        $errorList->expects($this->never())
            ->method('push');

        $result->expects($this->never())
            ->method('pushError');

        $result->expects($this->never())
            ->method('pushErrors');

        $this->validationModule->expects($this->once())
            ->method('buildValidationResult')
            ->with(array(
                'validator' => $validator,
                'errors' => $errorList
            ))
            ->will($this->returnValue($result));

        return $result;
    }
}