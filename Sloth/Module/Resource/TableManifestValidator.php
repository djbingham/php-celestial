<?php
namespace Sloth\Module\Resource;

class TableManifestValidator
{
    private $errors = array();

	public function validate(\stdClass $manifest)
    {
        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
