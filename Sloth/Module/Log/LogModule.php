<?php
namespace Sloth\Module\Log;

use Monolog\Logger;
use Sloth\Exception\InvalidArgumentException;

class LogModule
{
	/**
	 * @var array
	 */
	private $loggers = [];

	public function setLogger($name, Logger $logger)
	{
		$this->loggers[$name] = $logger;
		return $this;
	}

	public function getLogger($name = 'default')
	{
		if (!isset($this->loggers[$name])) {
			throw new InvalidArgumentException('No log found with name `%s`.', $name);
		}
		return $this->loggers[$name];
	}

	/**
	 * Write to all logs with a common message (and optional data)
	 *
	 * @param string $message
	 * @param array $context
	 * @param int $level
	 * @return $this
	 */
	public function log($message, array $context = [], $level = Logger::INFO)
	{
		/** @var Logger $logger */
		foreach ($this->loggers as $logger) {
			$logger->log($level, $message, $context);
		}
		return $this;
	}
}
