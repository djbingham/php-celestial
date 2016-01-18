<?php

namespace Sloth\Module\Data\TableValidation\Test\Unit\Validator\Field\Property;

use Sloth\Module\Data\TableValidation\Test\UnitTest;
use Sloth\Module\Data\TableValidation\Validator\Field\Property\ValidatorListValidator;

class ValidatorListValidatorTest extends UnitTest
{
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

    public function testValidatorListMustNotBeArray()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForSingleError(
            $validatorListValidator,
            $validationResult,
            'Field validators must be an array'
        );

        $result = $validatorListValidator->validate(array());

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorListMustNotBeString()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForSingleError(
            $validatorListValidator,
            $validationResult,
            'Field validators must be an array'
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
            'Field validators must be an array'
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
            'Field validators must be an array'
        );

        $result = $validatorListValidator->validate(true);

        $this->assertSame($validationResult, $result);
    }

    public function testValidatorListMayBeAnEmptyPlainObject()
    {
        $validatorListValidator = new ValidatorListValidator($this->dependencyManager);
        $validationResult = $this->mockValidationResult();

        $this->setupMockExpectationsForNoErrors($validatorListValidator, $validationResult);

        $result = $validatorListValidator->validate(new \stdClass());

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

        $result = $validatorListValidator->validate((object)array(
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

        $result = $validatorListValidator->validate((object)array(
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

        $result = $validatorListValidator->validate((object)array(
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

        $result = $validatorListValidator->validate((object)array(
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

        $result = $validatorListValidator->validate((object)array(
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
}