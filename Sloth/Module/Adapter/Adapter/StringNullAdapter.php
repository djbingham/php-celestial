<?php
namespace Sloth\Module\Adapter\Adapter;

use Sloth\Module\Adapter\Base\AbstractArrayAdapter;

class StringNullAdapter extends AbstractArrayAdapter
{
	protected function adaptFieldValue($value)
	{
		$adaptedValue = null;
		switch ($value) {
			case 'null':
				return null;
				break;
			default:
				$adaptedValue = $value;
				break;
		}
		return $adaptedValue;
	}
}
