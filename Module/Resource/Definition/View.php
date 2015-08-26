<?php
namespace Sloth\Module\Resource\Definition;

class View
{
    /**
     * @var string
     */
	private $path;

    public function __construct(array $properties)
    {
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    public function getPath()
    {
        return $this->path;
    }
}
