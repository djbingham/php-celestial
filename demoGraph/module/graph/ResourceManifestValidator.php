<?php
namespace DemoGraph\Module\Graph;

class ResourceManifestValidator
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
