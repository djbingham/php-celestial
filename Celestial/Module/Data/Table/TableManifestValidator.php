<?php
namespace Celestial\Module\Data\Table;

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
