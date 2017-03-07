<?php
namespace Celestial\Module\Log\Test\Unit;

require_once dirname(dirname(__DIR__)) . '/UnitTest.php';

use Monolog\Logger;
use Celestial\Module\Log\Logger\ContextLogger;
use Celestial\Module\Log\Test\UnitTest;

class ContextLoggerTest extends UnitTest
{
	public function testLogParametersArePassedToDefinedLogger()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];
		$level = Logger::DEBUG;

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$logWriters[0]->expects($this->once())
			->method('log')
			->with($level, $message, $context);

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->log($level, $message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogParametersArePassedToAllDefinedLoggers()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar'
		];
		$level = Logger::INFO;

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with($level, $message, $context);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->log($level, $message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogMessageContextDefaultsToLoggerContext()
	{
		$message = 'Test message';
		$level = Logger::NOTICE;
		$loggerContext = [
			'f' => 'foo',
			'b' => 'bar'
		];

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with($level, $message, $loggerContext);
		}

		$contextLogger = new ContextLogger($logWriters, $loggerContext);

		$response = $contextLogger->log($level, $message);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogMessageContextDefaultsToEmptyArrayIfLoggerHasNoContext()
	{
		$message = 'Test message';
		$level = Logger::NOTICE;

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with($level, $message, []);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->log($level, $message);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogMessageContextIsMergedWithLoggerContext()
	{
		$loggerContext = [
			'f' => 'foo',
			'b' => 'bar'
		];
		$messageContext = [
			'fb' => 'foobar'
		];
		$combinedContext = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];
		$message = 'Test message';
		$level = Logger::INFO;

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with($level, $message, $combinedContext);
		}

		$contextLogger = new ContextLogger($logWriters, $loggerContext);

		$response = $contextLogger->log($level, $message, $messageContext);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogDebugPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::DEBUG, $message, $context);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->debug($message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogDebugDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::DEBUG, $message, []);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->debug($message);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogInfoPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::INFO, $message, $context);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->info($message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogInfoDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::INFO, $message, []);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->info($message);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogNoticePassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::NOTICE, $message, $context);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->notice($message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogNoticeDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::NOTICE, $message, []);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->notice($message);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogWarningPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::WARNING, $message, $context);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->warning($message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogWarningDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::WARNING, $message, []);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->warning($message);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogErrorPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::ERROR, $message, $context);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->error($message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogErrorDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::ERROR, $message, []);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->error($message);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogCriticalPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::CRITICAL, $message, $context);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->critical($message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogCriticalDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::CRITICAL, $message, []);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->critical($message);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogAlertPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::ALERT, $message, $context);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->alert($message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogAlertDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::ALERT, $message, []);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->alert($message);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogEmergencyPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$context = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::EMERGENCY, $message, $context);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->emergency($message, $context);

		$this->assertSame($contextLogger, $response);
	}

	public function testLogEmergencyDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$logWriters = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		foreach ($logWriters as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::EMERGENCY, $message, []);
		}

		$contextLogger = new ContextLogger($logWriters);

		$response = $contextLogger->emergency($message);

		$this->assertSame($contextLogger, $response);
	}
}
