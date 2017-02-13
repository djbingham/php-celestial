<?php
namespace Celestial\Module\Data\Resource;

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
