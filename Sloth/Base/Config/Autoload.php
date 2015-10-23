<?php
namespace Sloth\Base\Config;

use Sloth\Exception;

class Autoload
{
	private $namespace;
	private $directory;

	public function __construct(array $properties)
	{
		foreach ($properties as $name => $value) {
			if (!property_exists($this, $name)) {
				throw new Exception\InvalidArgumentException(
					sprintf('Unrecognised property given to autoload config: %s', $name)
				);
			}
			$this->$name = $value;
		}
	}

	public function getNamespace()
	{
		return $this->namespace;
	}

	public function getDirectory()
	{
		return $this->directory;
	}

	public function validate()
	{
		if (empty($this->namespace)) {
			throw new Exception\InvalidArgumentException('Missing namespace in autoload config');
		}
		if (!is_dir($this->directory)) {
			throw new Exception\InvalidArgumentException(
				sprintf(
					'Configured autoload directory does not exist for namespace `%s`. Directory: %s',
					$this->namespace,
					$this->directory
				)
			);
		}
		return true;
	}
}
