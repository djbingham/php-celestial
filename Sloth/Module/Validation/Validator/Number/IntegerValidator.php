<?php
namespace Sloth\Module\Validation\Validator\Number;

use Sloth\Module\Validation\Face\ValidatorInterface;

class IntegerValidator implements ValidatorInterface
{
	public function validate($value, array $options = array())
	{
		return is_int($value);
	}
}
