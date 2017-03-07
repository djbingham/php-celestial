<?php
namespace Celestial\Module\Log\Logger;

use Celestial\Exception\InvalidConfigurationException;

class ContextLogger implements \Celestial\Module\Log\Face\LoggerInterface
{
	/**
	 * @var array
	 */
	private $writers;

	/**
	 * @var array
	 */
	private $context;

	/**
	 * ContextLogger constructor. Logs each message with a class identifier to aid debugging.
	 *
	 * @param array $writers List of Monolog\Logger instances to write to.
	 * @param array $context Elements to include in the context of each log message.
	 */
	public function __construct(array $writers, array $context = [])
	{
		$this->validateLogWriters($writers);

		$this->writers = $writers;
		$this->context = $context;
	}

	public function log($level, $message, array $context = [])
	{
		$context = array_merge($this->context, $context);

		/** @var \Monolog\Logger $writer */
		foreach ($this->writers as $writer) {
			$writer->log($level, $message, $context);
		}

		return $this;
	}

	public function debug($message, array $context = [])
	{
		return $this->log(\Monolog\Logger::DEBUG, $message, $context);
	}

	public function info($message, array $context = [])
	{
		return $this->log(\Monolog\Logger::INFO, $message, $context);
	}

	public function notice($message, array $context = [])
	{
		return $this->log(\Monolog\Logger::NOTICE, $message, $context);
	}

	public function warning($message, array $context = [])
	{
		return $this->log(\Monolog\Logger::WARNING, $message, $context);
	}

	public function error($message, array $context = [])
	{
		return $this->log(\Monolog\Logger::ERROR, $message, $context);
	}

	public function critical($message, array $context = [])
	{
		return $this->log(\Monolog\Logger::CRITICAL, $message, $context);
	}

	public function alert($message, array $context = [])
	{
		return $this->log(\Monolog\Logger::ALERT, $message, $context);
	}

	public function emergency($message, array $context = [])
	{
		return $this->log(\Monolog\Logger::EMERGENCY, $message, $context);
	}

	private function validateLogWriters(array $writers)
	{
		foreach ($writers as $name => $writer) {
			if (!($writer instanceof \Monolog\Logger)) {
				throw new InvalidConfigurationException(
					sprintf('Log writer `%s` is invalid. Must be an instance of Monolog/Logger', $name)
				);
			}
		}
	}
}
