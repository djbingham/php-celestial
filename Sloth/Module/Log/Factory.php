<?php
namespace Sloth\Module\Log;

use Cascade\Cascade;
use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		$module = new LogModule();

		Cascade::fileConfig($this->options);

		foreach (array_keys($this->options['loggers']) as $loggerName) {
			$module->setLogger($loggerName, Cascade::logger($loggerName));
		}

		return $module;
	}

	protected function validateOptions()
	{
		// Monolog-Cascade should handle validation on config load
	}
}