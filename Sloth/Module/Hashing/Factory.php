<?php
namespace Sloth\Module\Hashing;

use Sloth\Base\AbstractModuleFactory;
use Sloth\Exception\InvalidArgumentException;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		$module = new HashingModule();

		$module->setSalt($this->options['salt']);

		if (array_key_exists('defaultAlgorithm', $this->options)) {
			$module->setDefaultAlgorithm($this->options['defaultAlgorithm']);
		}

		return $module;
	}

	protected function validateOptions()
	{
		$required = array('salt');

		$missing = array_diff($required, array_keys($this->options));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Hashing module: ' . implode(', ', $missing)
			);
		}
	}
}
