<?php
namespace Sloth\Module\Validation\Face;

interface ValidatorInterface
{
	public function validate($input, array $options = array());
}