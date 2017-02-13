<?php
namespace Celestial\Module\Adapter\Adapter;

use Celestial\Module\Adapter\Base\AbstractArrayAdapter;

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
