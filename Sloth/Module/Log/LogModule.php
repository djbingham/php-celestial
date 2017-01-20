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
	 * Write to all logs with a common message, contextual data and reporting level
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

	public function logDebug($message, array $context = [])
	{
		return $this->log($message, $context, Logger::DEBUG);
	}

	public function logInfo($message, array $context = [])
	{
		return $this->log($message, $context, Logger::INFO);
	}

	public function logNotice($message, array $context = [])
	{
		return $this->log($message, $context, Logger::NOTICE);
	}

	public function logWarning($message, array $context = [])
	{
		return $this->log($message, $context, Logger::WARNING);
	}

	public function logError($message, array $context = [])
	{
		return $this->log($message, $context, Logger::ERROR);
	}

	public function logCritical($message, array $context = [])
	{
		return $this->log($message, $context, Logger::CRITICAL);
	}

	public function logAlert($message, array $context = [])
	{
		return $this->log($message, $context, Logger::ALERT);
	}

	public function logEmergency($message, array $context = [])
	{
		return $this->log($message, $context, Logger::EMERGENCY);
	}
}
