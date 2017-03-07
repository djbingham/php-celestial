<?php
namespace Celestial\Module\Log;

use Celestial\Exception\InvalidArgumentException;

class LogModule
{
	/**
	 * @var array
	 */
	private $logWriters = [];

	public function setLogWriter($name, \Monolog\Logger $writer)
	{
		$this->logWriters[$name] = $writer;
		return $this;
	}

	public function getLogWriter($name = 'default')
	{
		if (!isset($this->logWriters[$name])) {

			throw new InvalidArgumentException('No log writer found with name `%s`.', $name);
		}

		return $this->logWriters[$name];
	}

	public function createLogger($context = [])
	{
		switch (true) {

			case $context === []:
				$contextArray = [];
				break;

			case is_array($context) || ($context instanceof \StdClass):
				$contextArray = (array) $context;
				break;

			case is_object($context) && !($context instanceof \StdClass):
				$contextArray = ['logSource' => get_class($context)];
				break;

			default:
				$contextArray = ['logSource' => (string) $context];
				break;
		}

		return new Logger\ContextLogger($this->logWriters, $contextArray);
	}
}
