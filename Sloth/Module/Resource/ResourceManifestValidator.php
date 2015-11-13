<?php
namespace Sloth\Module\Resource;

class ResourceManifestValidator
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
