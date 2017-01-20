<?php
namespace Sloth\Module\Log\Test\Unit;

require_once dirname(__DIR__) . '/UnitTest.php';

use Monolog\Logger;
use Sloth\Module\Log\LogModule;
use Sloth\Module\Log\Test\UnitTest;

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

	public function testSetLoggerReturnsFluentInterface()
	{
		$logger = $this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock();
		$response = $this->logModule->setLogger('myFirstLogger', $logger);
		$this->assertSame($this->logModule, $response);
	}

	public function testLogParametersArePassedToDefinedLogger()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];
		$level = Logger::DEBUG;


		$logger = $this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock();

		$this->logModule->setLogger('myFirstLogger', $logger);

		$logger->expects($this->once())
			->method('log')
			->with($level, $message, $data);

		$response = $this->logModule->log($message, $data, $level);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogParametersArePassedToAllDefinedLoggers()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];
		$level = Logger::ERROR;

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with($level, $message, $data);
		}

		$response = $this->logModule->log($message, $data, $level);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogDataDefaultsToNullAndLevelDefaultsToInfo()
	{
		$message = 'Test message';

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::INFO, $message, []);
		}

		$response = $this->logModule->log($message);

		$this->assertSame($this->logModule, $response);
	}

	public function testLoggerCanBeOverwrittenByName()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];
		$level = Logger::ALERT;

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myFirstLogger', $loggers[2]);

		$loggers[0]->expects($this->never())
			->method('log');

		foreach ([$loggers[1], $loggers[2]] as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with($level, $message, $data);
		}

		$response = $this->logModule->log($message, $data, $level);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogDebugPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::DEBUG, $message, $data);
		}

		$response = $this->logModule->logDebug($message, $data);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogDebugDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::DEBUG, $message);
		}

		$response = $this->logModule->logDebug($message, []);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogInfoPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::INFO, $message, $data);
		}

		$response = $this->logModule->logInfo($message, $data);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogInfoDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::INFO, $message);
		}

		$response = $this->logModule->logInfo($message, []);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogNoticePassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::NOTICE, $message, $data);
		}

		$response = $this->logModule->logNotice($message, $data);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogNoticeDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::NOTICE, $message);
		}

		$response = $this->logModule->logNotice($message, []);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogWarningPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::WARNING, $message, $data);
		}

		$response = $this->logModule->logWarning($message, $data);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogWarningDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::WARNING, $message);
		}

		$response = $this->logModule->logWarning($message, []);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogErrorPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::ERROR, $message, $data);
		}

		$response = $this->logModule->logError($message, $data);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogErrorDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::ERROR, $message);
		}

		$response = $this->logModule->logError($message, []);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogCriticalPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::CRITICAL, $message, $data);
		}

		$response = $this->logModule->logCritical($message, $data);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogCriticalDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::CRITICAL, $message);
		}

		$response = $this->logModule->logCritical($message, []);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogAlertPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::ALERT, $message, $data);
		}

		$response = $this->logModule->logAlert($message, $data);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogAlertDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::ALERT, $message);
		}

		$response = $this->logModule->logAlert($message, []);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogEmergencyPassesMessageAndContextToAllLoggersWithDebugReportingLevel()
	{
		$message = 'Test message';
		$data = [
			'f' => 'foo',
			'b' => 'bar',
			'fb' => 'foobar'
		];

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::EMERGENCY, $message, $data);
		}

		$response = $this->logModule->logEmergency($message, $data);

		$this->assertSame($this->logModule, $response);
	}

	public function testLogEmergencyDefaultsContextToEmptyArray()
	{
		$message = 'Test message';

		$loggers = [
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('Monolog\\Logger')->disableOriginalConstructor()->getMock()
		];

		$this->logModule->setLogger('myFirstLogger', $loggers[0]);
		$this->logModule->setLogger('mySecondLogger', $loggers[1]);
		$this->logModule->setLogger('myThirdLogger', $loggers[2]);

		foreach ($loggers as $logger) {
			$logger->expects($this->once())
				->method('log')
				->with(Logger::EMERGENCY, $message);
		}

		$response = $this->logModule->logEmergency($message, []);

		$this->assertSame($this->logModule, $response);
	}
}
