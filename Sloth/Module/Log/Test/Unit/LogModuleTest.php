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
}
