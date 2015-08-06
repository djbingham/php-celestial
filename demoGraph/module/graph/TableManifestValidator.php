<?php
namespace DemoGraph\Module\Graph;

class TableManifestValidator
{
    private $errors = array();

	public function validate(array $manifest)
    {
        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
