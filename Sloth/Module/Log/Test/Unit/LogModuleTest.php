<?php
namespace Sloth\Module\Log\Test\Unit;

require_once dirname(__DIR__) . '/UnitTest.php';

use Monolog\Logger;
use Sloth\Module\Log\LogModule;
use Sloth\Module\Log\Test\UnitTest;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class LogModuleTest extends UnitTest
{
	/**
	 * @var LogModule
	 */
	private $logModule;

	public function setUp()
	{
		$this->logModule = new LogModule();
	}

	public function testSetLogWriterReturnsFluentInterface()
	{
		$logWriter = $this->createLogWriterMock();

		$logger = $this->logModule->setLogWriter('myFirstLogger', $logWriter);

		$this->assertSame($this->logModule, $logger);
	}

	public function testGetLogWriterReturnsLoggerByName()
	{
		$logWriters = [
			$this->createLogWriterMock(),
			$this->createLogWriterMock()
		];

		$this->logModule->setLogWriter('myFirstLogger', $logWriters[0]);
		$this->logModule->setLogWriter('mySecondLogger', $logWriters[1]);

		$logger = $this->logModule->getLogWriter('myFirstLogger');
		$this->assertSame($logWriters[0], $logger);

		$logger = $this->logModule->getLogWriter('mySecondLogger');

		$this->assertSame($logWriters[1], $logger);
	}

	public function testSetLogWriterOverwritesExistingLoggerByName()
	{
		$logWriters = [
			$this->createLogWriterMock(),
			$this->createLogWriterMock(),
			$this->createLogWriterMock()
		];

		$this->logModule->setLogWriter('myFirstLogger', $logWriters[0]);
		$this->logModule->setLogWriter('mySecondLogger', $logWriters[1]);
		$this->logModule->setLogWriter('myFirstLogger', $logWriters[2]);

		$logger = $this->logModule->getLogWriter('myFirstLogger');

		$this->assertSame($logWriters[2], $logger);
	}

	public function testCreateLoggerReturnsNewLoggerInstance()
	{
		$logWriter = $this->createLogWriterMock();

		$this->logModule->setLogWriter('myFirstLogger', $logWriter);

		$logger = $this->logModule->createLogger();

		$this->assertInstanceOf('Sloth\\Module\\Log\\Logger\\ContextLogger', $logger);
	}

	public function testCreateLoggerDefaultsContextToEmptyArray()
	{
		$logLevel = Logger::DEBUG;
		$logMessage = 'Test message';

		$logWriter = $this->createLogWriterMock();

		$this->logModule->setLogWriter('myFirstLogger', $logWriter);

		$logger = $this->logModule->createLogger();

		$this->assertInstanceOf('Sloth\\Module\\Log\\Logger\\ContextLogger', $logger);

		$this->expectLogToBeWrittenTo($logWriter, $logLevel, $logMessage, []);
		$logger->log(Logger::DEBUG, $logMessage);
	}

	public function testCreateLoggerSendsArrayContextToLogger()
	{
		$logLevel = Logger::DEBUG;
		$logMessage = 'Test message';
		$logContext = ['test' => 'param'];

		$logWriter = $this->createLogWriterMock();

		$this->logModule->setLogWriter('myFirstLogger', $logWriter);

		$logger = $this->logModule->createLogger($logContext);

		$this->assertInstanceOf('Sloth\\Module\\Log\\Logger\\ContextLogger', $logger);

		$this->expectLogToBeWrittenTo($logWriter, $logLevel, $logMessage, $logContext);
		$logger->log(Logger::DEBUG, $logMessage);
	}

	public function testCreateLoggerConvertsStdClassContextToArray()
	{
		$logLevel = Logger::DEBUG;
		$logMessage = 'Test message';
		$logContext = (object) ['test' => 'param'];

		$logWriter = $this->createLogWriterMock();

		$this->logModule->setLogWriter('myFirstLogger', $logWriter);

		$logger = $this->logModule->createLogger($logContext);

		$this->assertInstanceOf('Sloth\\Module\\Log\\Logger\\ContextLogger', $logger);

		$this->expectLogToBeWrittenTo($logWriter, $logLevel, $logMessage, (array) $logContext);
		$logger->log(Logger::DEBUG, $logMessage);
	}

	public function testCreateLoggerSendsNameOfContextClassInstanceToLoggerAsLogSourceInContextArray()
	{
		$logLevel = Logger::DEBUG;
		$logMessage = 'Test message';
		$context = $this;
		$expectedLogContext = ['logSource' => get_class($this)];

		$logWriter = $this->createLogWriterMock();

		$this->logModule->setLogWriter('myFirstLogger', $logWriter);

		$logger = $this->logModule->createLogger($context);

		$this->assertInstanceOf('Sloth\\Module\\Log\\Logger\\ContextLogger', $logger);

		$this->expectLogToBeWrittenTo($logWriter, $logLevel, $logMessage, $expectedLogContext);
		$logger->log(Logger::DEBUG, $logMessage);
	}

	public function testCreateLoggerSendsStringContextAsLogSourceInContextArray()
	{
		$logLevel = Logger::DEBUG;
		$logMessage = 'Test message';
		$context = 'Context string';
		$expectedLogContext = ['logSource' => $context];

		$logWriter = $this->createLogWriterMock();

		$this->logModule->setLogWriter('myFirstLogger', $logWriter);

		$logger = $this->logModule->createLogger($context);

		$this->assertInstanceOf('Sloth\\Module\\Log\\Logger\\ContextLogger', $logger);

		$this->expectLogToBeWrittenTo($logWriter, $logLevel, $logMessage, $expectedLogContext);
		$logger->log(Logger::DEBUG, $logMessage);
	}

	public function testCreateLoggerSendsNumberContextAsLogSourceStringInContextArray()
	{
		$logLevel = Logger::DEBUG;
		$logMessage = 'Test message';
		$context = 21.92;
		$expectedLogContext = ['logSource' => '21.92'];

		$logWriter = $this->createLogWriterMock();

		$this->logModule->setLogWriter('myFirstLogger', $logWriter);

		$logger = $this->logModule->createLogger($context);

		$this->assertInstanceOf('Sloth\\Module\\Log\\Logger\\ContextLogger', $logger);

		$this->expectLogToBeWrittenTo($logWriter, $logLevel, $logMessage, $expectedLogContext);
		$logger->log(Logger::DEBUG, $logMessage);
	}

	public function testCreateLoggerSendsEmptyStringAsLogSourceInContextArrayIfGivenNullContext()
	{
		$logLevel = Logger::DEBUG;
		$logMessage = 'Test message';
		$context = null;
		$expectedLogContext = ['logSource' => ''];

		$logWriter = $this->createLogWriterMock();

		$this->logModule->setLogWriter('myFirstLogger', $logWriter);

		$logger = $this->logModule->createLogger($context);

		$this->assertInstanceOf('Sloth\\Module\\Log\\Logger\\ContextLogger', $logger);

		$this->expectLogToBeWrittenTo($logWriter, $logLevel, $logMessage, $expectedLogContext);
		$logger->log(Logger::DEBUG, $logMessage);
	}

	/**
	 * @return Logger|MockObject
	 */
	private function createLogWriterMock()
	{
		return $this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock();
	}

	private function expectLogToBeWrittenTo(MockObject $logWriter, $level, $message, array $context)
	{
		$logWriter->expects($this->once())
			->method('log')
			->with($level, $message, $context);
	}
}
