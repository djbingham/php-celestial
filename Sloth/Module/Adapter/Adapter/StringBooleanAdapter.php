<?php
namespace Sloth\Module\Adapter\Adapter;

use Sloth\Module\Adapter\Base\AbstractArrayAdapter;

class StringBooleanAdapter extends AbstractArrayAdapter
{
	protected function adaptFieldValue($value)
	{
		$adaptedValue = null;
		switch ($value) {
			case 'true':
				$adaptedValue = true;
				break;
			case 'false':
				$adaptedValue = false;
				break;
			default:
				$adaptedValue = $value;
				break;
		}
		return $adaptedValue;
	}
}
