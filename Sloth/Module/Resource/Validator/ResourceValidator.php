<?php
namespace Sloth\Module\Resource\Validator;

use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Resource\Definition;
use Sloth\Module\Resource\Face\ResourceValidatorInterface;
use Sloth\Module\Validation\ValidationModule;

class ResourceValidator implements ResourceValidatorInterface
{
	/**
	 * @var ValidationModule
	 */
	private $validationModule;

	public function __construct(ValidationModule $validationModule)
	{
		$this->validationModule = $validationModule;
	}

	public function validate(Definition\Resource $resourceDefinition, array $attributeValues)
	{
		$isValid = true;

		/** @var Definition\Validator $validatorDefinition */
		foreach ($resourceDefinition->validators as $validatorDefinition) {
			$validator = $this->validationModule->getValidator($validatorDefinition->rule);

			$attributesToTest = array();
			if (is_object($validatorDefinition->attributes)) {
				foreach ($validatorDefinition->attributes as $attributeLabel => $attributeName) {
					if (is_array($attributeName)) {
						$attributesToTest[$attributeLabel] = array();
						foreach ($attributeName as $subAttributeName) {
							$attributesToTest[$attributeLabel] = array_merge($attributesToTest[$attributeLabel], $this->getAttributeValue($subAttributeName, $attributeValues));
						}
					} else {
						$attributesToTest[$attributeLabel] = $this->getAttributeValue($attributeName, $attributeValues);
					}
				}
				$attributesToTest = array($attributesToTest);
			} elseif (is_array($validatorDefinition->attributes)) {
				foreach ($validatorDefinition->attributes as $attributeName) {
					$attributesToTest[] = $this->getAttributeValue($attributeName, $attributeValues);
				}
			} else {
				$attributesToTest[] = $this->getAttributeValue($validatorDefinition->attributes, $attributeValues);
			}

			foreach ($attributesToTest as $attribute) {
				$validatorPassed = $validator->validate($attribute, (array)$validatorDefinition->options);

				if ($validatorDefinition->negate === true) {
					$validatorPassed = !$validatorPassed;
				}

				if ($validatorPassed !== true) {
					$isValid = false;
					break(2);
				}
			}
		}

		return $isValid;
	}

	protected function getAttributeValue($flattenedAttributeName, $attributeValues)
	{
		$attributeNameParts = explode('.', $flattenedAttributeName);
		$firstPart = array_shift($attributeNameParts);

		if (count($attributeNameParts) > 0) {
			$subAttributes = $attributeValues[$firstPart];

			if (is_array($subAttributes)) {
				/*
					If sub-attributes is numerically indexed, it must be from a one-to-many relationship,
					since all attributes are keyed by attribute name, which is always a string.
				*/
				if (array_keys($subAttributes)[0] === 0) {
					$value = array();
					foreach ($subAttributes as $index => $subAttributesRow) {
						$value[] = $this->getAttributeValue(implode('.', $attributeNameParts), $subAttributesRow);
					}
				} else {
					$value = $this->getAttributeValue(implode('.', $attributeNameParts), $subAttributes);
				}
			} else {
				throw new InvalidRequestException('Attributes do not match resource definition');
			}
		} else {
			$value = $attributeValues[$firstPart];
		}

		return $value;
	}

	protected function flattenAttributes(array $attributes, $prefix = null)
	{
		$flattenedAttributes = array();

		foreach ($attributes as $attributeName => $attributeValue) {
			if ($prefix !== null) {
				$prefixedAttributeName = $prefix . '.' . $attributeName;
			} else {
				$prefixedAttributeName = $attributeName;
			}

			if (is_array($attributeValue)) {
				$subAttributes = $this->flattenAttributes($attributeValue, $prefixedAttributeName);
				$flattenedAttributes = array_merge($flattenedAttributes, $subAttributes);
			} else {
				$flattenedAttributes[$prefixedAttributeName] = $attributeValue;
			}
		}

		return $flattenedAttributes;
	}
}
