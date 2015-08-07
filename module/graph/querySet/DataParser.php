<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Definition;

class DataParser
{
	public function extractLinkListData(Definition\Table\JoinList $links, array $data)
	{
		$linkData = array();
		foreach ($links as $link) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join $link */
			foreach ($this->extractLinkData($link, $data) as $fieldName => $value) {
				$linkData[$link->getChildTable()->getAlias()][$fieldName] = $value;
			}
		}
		return $linkData;
	}

	public function extractLinkData(Definition\Table\Join $link, array $data)
	{
		$linkData = array();
		foreach ($link->constraints as $constraint) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join\Constraint $constraint */
			if ($constraint->subJoins instanceof Definition\Table\Join\SubJoinList && $constraint->subJoins->length() > 0) {
				foreach ($constraint->subJoins as $subJoin) {
					/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $subJoin */
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

	public function formatResourceData(array $rawData, Definition\Table $resourceDefinition)
	{
		$resourceData = $this->extractResourceData($resourceDefinition, $rawData);
		return $resourceData;
	}

	private function extractResourceData(Definition\Table $resourceDefinition, array $rawData, array $filters = array())
	{
		$attributeData = array();
		foreach ($rawData[$resourceDefinition->getAlias()] as $rowIndex => $rowData) {
			/** @var \Sloth\Module\Graph\Definition\Table\Field $attribute */
			if ($this->rowMatchesExpectedData($rowData, $filters)) {
				foreach ($resourceDefinition->fields as $attribute) {
					$attributeAlias = $attribute->getAlias();
					if (array_key_exists($attributeAlias, $rowData)) {
						$attributeData[$rowIndex][$attribute->name] = $rowData[$attributeAlias];
					}
				}
			}
			/** @var \Sloth\Module\Graph\Definition\Table\Join $link */
			foreach ($resourceDefinition->links as $link) {
				if (in_array($link->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
					foreach ($link->getChildTable()->fields as $attribute) {
						$attributeAlias = $attribute->getAlias();
						if (array_key_exists($attributeAlias, $rowData)) {
							$attributeData[$rowIndex][$link->name][$attribute->name] = $rowData[$attributeAlias];
						}
					}
				} else {
					$linkFilters = $this->getLinkData($link, $rowData);
					if ($this->rowMatchesExpectedData($rowData, $filters)) {
						$childData = $this->extractResourceData($link->getChildTable(), $rawData, $linkFilters);

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

	private function getLinkData(Definition\Table\Join $link, array $parentRowData)
	{
		$linkData = array();
		/** @var \Sloth\Module\Graph\Definition\Table\Join\Constraint $constraint */
		foreach ($link->getConstraints() as $constraint) {
			if ($constraint->subJoins !== null && $constraint->subJoins->length() > 0) {
				/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $subJoin */
				foreach ($constraint->subJoins as $subJoin) {
					$parentAlias = $subJoin->parentAttribute->getAlias();
					$childAlias = $subJoin->childAttribute->getAlias();
					$value = $parentRowData[$parentAlias];
					if ($subJoin->parentTable->getAlias() === $link->parentTable->getAlias()) {
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
