<?php

namespace Sloth\Test\Unit\Database\Connector;

require_once dirname(dirname(dirname(__DIR__))) . '/bootstrap.php';

use Sloth\Test\Unit\Database\UnitTest;
use Database\Connector\ConnectionOptions;

/**
 * ConnectionOptionsTest
 * @package Test\Unit\Database\Connection\MySqli
 * @author Favicon, 2013
 */
class ConnectionOptionsTest extends UnitTest
{
	protected $object;

	public function setUp()
	{
		$this->object = new ConnectionOptions();
	}

	public function testMagicGetFailsIfPropertyNotDefined()
	{
		$this->setExpectedException('\Exception');
		$this->object->notanattribute;
	}

	public function testMagicSetFailsIfPropertyNotDefined()
	{
		$this->setExpectedException('\Exception');
		$this->object->notanattribute = 'test';
	}

	public function testSetAndGetDatabaseName()
	{
		$testData = 'dbName';
		$this->object->databaseName = $testData;
		$this->assertEquals($testData, $this->object->databaseName);
		$this->assertEquals($testData, $this->object->getDatabaseName());
	}

	public function testSetAndGetHost()
	{
		$testData = 'hostName';
		$this->object->host = $testData;
		$this->assertEquals($testData, $this->object->host);
		$this->assertEquals($testData, $this->object->getHost());
	}

	public function testSetAndGetPassword()
	{
		$testData = 'pwd';
		$this->object->password = $testData;
		$this->assertEquals($testData, $this->object->password);
		$this->assertEquals($testData, $this->object->getPassword());
	}

	public function testSetAndGetPort()
	{
		$testData = 1000;
		$this->object->port = $testData;
		$this->assertEquals($testData, $this->object->port);
		$this->assertEquals($testData, $this->object->getPort());
	}

	public function testSetAndGetSocket()
	{
		$testData = 10;
		$this->object->socket = $testData;
		$this->assertEquals($testData, $this->object->socket);
		$this->assertEquals($testData, $this->object->getSocket());
	}

	public function testSetAndGetUsername()
	{
		$testData = 'user';
		$this->object->username = $testData;
		$this->assertEquals($testData, $this->object->username);
		$this->assertEquals($testData, $this->object->getUsername());
	}

	public function testValidateDatabaseNameRejectsEmptyValue()
	{
		$output = $this->object->validateDatabaseName('');
		$this->assertFalse($output);
	}

	public function testValidateDatabaseNameRejectsObject()
	{
		$output = $this->object->validateDatabaseName(new self);
		$this->assertFalse($output);
	}

	public function testValidateDatabaseNameRejectsArray()
	{
		$output = $this->object->validateDatabaseName(array());
		$this->assertFalse($output);
	}

	public function testValidateHostRejectsEmptyValue()
	{
		$output = $this->object->validateHost('');
		$this->assertFalse($output);
	}

	public function testValidateHostRejectsObject()
	{
		$output = $this->object->validateHost(new self);
		$this->assertFalse($output);
	}

	public function testValidateHostRejectsArray()
	{
		$output = $this->object->validateHost(array());
		$this->assertFalse($output);
	}

	public function testValidatePasswordRejectsEmptyValue()
	{
		$output = $this->object->validatePassword('');
		$this->assertFalse($output);
	}

	public function testValidatePasswordRejectsObject()
	{
		$output = $this->object->validatePassword(new self);
		$this->assertFalse($output);
	}

	public function testValidatePasswordRejectsArray()
	{
		$output = $this->object->validatePassword(array());
		$this->assertFalse($output);
	}

	public function testValidatePortRejectsEmptyValue()
	{
		$output = $this->object->validatePort('');
		$this->assertFalse($output);
	}

	public function testValidatePortRejectsObject()
	{
		$output = $this->object->validatePort(new self);
		$this->assertFalse($output);
	}

	public function testValidatePortRejectsArray()
	{
		$output = $this->object->validatePort(array());
		$this->assertFalse($output);
	}

	public function testValidatePortRejectsFloat()
	{
		$output = $this->object->validatePort(7.2);
		$this->assertFalse($output);
	}

	public function testValidatePortRejectsTextString()
	{
		$output = $this->object->validatePort('someText');
		$this->assertFalse($output);
	}

	public function testValidateSocketRejectsEmptyValue()
	{
		$output = $this->object->validateSocket('');
		$this->assertFalse($output);
	}

	public function testValidateSocketRejectsObject()
	{
		$output = $this->object->validateSocket(new self);
		$this->assertFalse($output);
	}

	public function testValidateSocketRejectsArray()
	{
		$output = $this->object->validateSocket(array());
		$this->assertFalse($output);
	}

	public function testValidateSocketRejectsFloat()
	{
		$output = $this->object->validateSocket(7.2);
		$this->assertFalse($output);
	}

	public function testValidateSocketRejectsTextString()
	{
		$output = $this->object->validateSocket('someText');
		$this->assertFalse($output);
	}

	public function testValidateUsernameRejectsEmptyValue()
	{
		$output = $this->object->validateUsername('');
		$this->assertFalse($output);
	}

	public function testValidateUsernameRejectsObject()
	{
		$output = $this->object->validateUsername(new self);
		$this->assertFalse($output);
	}

	public function testValidateUsernameRejectsArray()
	{
		$output = $this->object->validateUsername(array());
		$this->assertFalse($output);
	}

	public function testValidate()
	{
		$this->object->databaseName = 'db';
		$this->object->host = 'hostname';
		$this->object->password = 'pwd';
		$this->object->port = 6000;
		$this->object->socket = 1;
		$this->object->username = 'uname';
		$this->assertTrue($this->object->validate());
		// Invalid database name
		$this->object->databaseName = array();
		$this->assertFalse($this->object->validate());
		$this->object->databaseName = 'db';
		// Invalid host
		$this->object->host = array();
		$this->assertFalse($this->object->validate());
		$this->object->host = 'hostname';
		// Invalid password
		$this->object->password = array();
		$this->assertFalse($this->object->validate());
		$this->object->password = 'pwd';
		// Invalid port
		$this->object->port = array();
		$this->assertFalse($this->object->validate());
		$this->object->port = 6000;
		// Invalid socket
		$this->object->socket = array();
		$this->assertFalse($this->object->validate());
		$this->object->socket = 1;
		// Invalid username
		$this->object->username = array();
		$this->assertFalse($this->object->validate());
		$this->object->username = 'uname';
		// Test still accepts valid object
		$this->assertTrue($this->object->validate());
	}

	public function testErrors()
	{
		$this->object->databaseName = 'db';
		$this->object->host = 'hostname';
		$this->object->password = 'pwd';
		$this->object->port = 6000;
		$this->object->socket = 1;
		$this->object->username = 'uname';
		$this->assertEmpty($this->object->errors());
		// Invalid database name
		$this->object->databaseName = array();
		$this->assertEquals(array(ConnectionOptions::ERROR_DATABASE_NAME), $this->object->errors());
		$this->object->databaseName = 'db';
		// Invalid host
		$this->object->host = array();
		$this->assertEquals(array(ConnectionOptions::ERROR_HOST), $this->object->errors());
		$this->object->host = 'hostname';
		// Invalid password
		$this->object->password = array();
		$this->assertEquals(array(ConnectionOptions::ERROR_PASSWORD), $this->object->errors());
		$this->object->password = 'pwd';
		// Invalid port
		$this->object->port = array();
		$this->assertEquals(array(ConnectionOptions::ERROR_PORT), $this->object->errors());
		$this->object->port = 6000;
		// Invalid socket
		$this->object->socket = array();
		$this->assertEquals(array(ConnectionOptions::ERROR_SOCKET), $this->object->errors());
		$this->object->socket = 1;
		// Invalid username
		$this->object->username = array();
		$this->assertEquals(array(ConnectionOptions::ERROR_USERNAME), $this->object->errors());
		$this->object->username = 'uname';
		// Test still accepts valid object
		$this->assertEmpty($this->object->errors());
	}
}
