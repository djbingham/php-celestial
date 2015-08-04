<?php
namespace DemoGraph\Module\Graph\QuerySet;

use DemoGraph\Module\Graph\ResourceDefinition;

class DataParser
{
	public function extractLinkListData(ResourceDefinition\LinkList $links, array $data)
	{
		$linkData = array();
		foreach ($links as $link) {
			/** @var ResourceDefinition\Link $link */
			foreach ($this->extractLinkData($link, $data) as $fieldName => $value) {
				$linkData[$link->getChildResource()->getAlias()][$fieldName] = $value;
			}
		}
		return $linkData;
	}

	public function extractLinkData(ResourceDefinition\Link $link, array $data)
	{
		$linkData = array();
		foreach ($link->constraints as $constraint) {
			/** @var ResourceDefinition\LinkConstraint $constraint */
			if ($constraint->subJoins instanceof ResourceDefinition\LinkSubJoinList && $constraint->subJoins->length() > 0) {
				foreach ($constraint->subJoins as $subJoin) {
					/** @var ResourceDefinition\LinkSubJoin $subJoin */
					$parentFieldAlias = $subJoin->parentAttribute->getAlias();
					$childFieldAlias = $subJoin->childAttribute->getAlias();
					$values = $this->getFieldValues($parentFieldAlias, $data);
					if (!empty($values)) {
						$linkData[$childFieldAlias] = $values;
					}
				}
			} else {
				$parentFieldAlias = $constraint->parentAttribute->getAlias();
				$childFieldAlias = $constraint->childAttribute->getAlias();
				$values = $this->getFieldValues($parentFieldAlias, $data);
				$linkData[$childFieldAlias] = $values;
			}
		}
		return $linkData;
	}

	public function getFieldValues($fieldName, array $data)
	{
		$values = array();
		foreach ($data as $row) {
			if (array_key_exists($fieldName, $row)) {
				$values[] = $row[$fieldName];
			}
		}
		return $values;
	}

	public function formatResourceData(array $rawData, ResourceDefinition\Resource $resourceDefinition)
	{
		$resourceData = $this->extractResourceData($resourceDefinition, $rawData);
		return $resourceData;
	}

	private function extractResourceData(ResourceDefinition\Resource $resourceDefinition, array $rawData, array $filters = array())
	{
		$attributeData = array();
		foreach ($rawData[$resourceDefinition->getAlias()] as $rowIndex => $rowData) {
			/** @var ResourceDefinition\Attribute $attribute */
			if ($this->rowMatchesExpectedData($rowData, $filters)) {
				foreach ($resourceDefinition->attributes as $attribute) {
					$attributeAlias = $attribute->getAlias();
					if (array_key_exists($attributeAlias, $rowData)) {
						$attributeData[$rowIndex][$attribute->name] = $rowData[$attributeAlias];
					}
				}
			}
			/** @var ResourceDefinition\Link $link */
			foreach ($resourceDefinition->links as $link) {
				if (in_array($link->type, array(ResourceDefinition\Link::ONE_TO_ONE, ResourceDefinition\Link::MANY_TO_ONE))) {
					foreach ($link->getChildResource()->attributes as $attribute) {
						$attributeAlias = $attribute->getAlias();
						if (array_key_exists($attributeAlias, $rowData)) {
							$attributeData[$rowIndex][$link->name][$attribute->name] = $rowData[$attributeAlias];
						}
					}
				} else {
					$linkFilters = $this->getLinkData($link, $rowData);
					if ($this->rowMatchesExpectedData($rowData, $filters)) {
						$childData = $this->extractResourceData($link->getChildResource(), $rawData, $linkFilters);

						$attributeData[$rowIndex][$link->name] = array();
						foreach ($childData as $childRow) {
							$attributeData[$rowIndex][$link->name][] = $childRow;
						}
					}
				}
			}
		}
		return $attributeData;
	}

	private function rowMatchesExpectedData(array $rowData, array $expectedValues)
	{
		$matches = 0;
		foreach ($expectedValues as $childAttributeAlias => $parentValue) {
			if ($rowData[$childAttributeAlias] === $parentValue) {
				$matches++;
			}
		}
		return $matches === count($expectedValues);
	}

	private function getLinkData(ResourceDefinition\Link $link, array $parentRowData)
	{
		$linkData = array();
		/** @var ResourceDefinition\LinkConstraint $constraint */
		foreach ($link->getConstraints() as $constraint) {
			if ($constraint->subJoins !== null && $constraint->subJoins->length() > 0) {
				/** @var ResourceDefinition\LinkSubJoin $subJoin */
				foreach ($constraint->subJoins as $subJoin) {
					$parentAlias = $subJoin->parentAttribute->getAlias();
					$childAlias = $subJoin->childAttribute->getAlias();
					$value = $parentRowData[$parentAlias];
					if ($subJoin->parentResource->getAlias() === $link->parentResource->getAlias()) {
						$linkData[$childAlias] = $value;
					}
				}
			} else {
				$parentAlias = $constraint->parentAttribute->getAlias();
				$childAlias = $constraint->childAttribute->getAlias();
				$value = $parentRowData[$parentAlias];
				$linkData[$childAlias] = $value;
			}
		}
		return $linkData;
	}
}
