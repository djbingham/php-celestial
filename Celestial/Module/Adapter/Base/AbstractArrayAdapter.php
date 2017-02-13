<?php
namespace Celestial\Module\Adapter\Base;

use Celestial\Module\Adapter\Face\AdapterInterface;

abstract class AbstractArrayAdapter implements AdapterInterface
{
	abstract protected function adaptFieldValue($value);

	public function adapt(array $rawData)
	{
		$adaptedData = array();

		foreach ($rawData as $fieldName => $value) {
			if (is_array($value)) {
				$adaptedData[$fieldName] = $this->adapt($value);
			} else {
				$adaptedData[$fieldName] = $this->adaptFieldValue($value);
			}
		}

		return $adaptedData;
	}
}
