<?php
namespace Sloth\Module\MySql;

use Helper\InternalCacheTrait;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Base\AbstractModuleFactory;
use SlothMySql\Connection\MySqli as DatabaseConnection;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Wrapper as QueryBuilderWrapper;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	public function initialise()
	{
		return new DatabaseWrapper($this->getConnection(), $this->getQueryBuilderFactory());
	}

	protected function validateOptions()
	{
		$required = array('host', 'name', 'username', 'password');

		$missing = array_diff($required, array_keys($this->options));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for MySql module: ' . implode(', ', $missing)
			);
		}

		if (!is_string($this->options['host'])) {
			throw new InvalidArgumentException('Invalid host given in options for MySql module');
		}

		if (!is_string($this->options['name'])) {
			throw new InvalidArgumentException('Invalid name given in options for MySql module');
		}

		if (!is_string($this->options['username'])) {
			throw new InvalidArgumentException('Invalid username given in options for MySql module');
		}

		if (!is_string($this->options['password'])) {
			throw new InvalidArgumentException('Invalid password given in options for MySql module');
		}

		if (array_key_exists('port', $this->options) && !is_string($this->options['port'])) {
			throw new InvalidArgumentException('Invalid port given in options for MySql module');
		}

		if (array_key_exists('socket', $this->options) && !is_string($this->options['socket'])) {
			throw new InvalidArgumentException('Invalid socket given in options for MySql module');
		}
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getConnection()
	{
		if (!$this->isCached('connection')) {
			$this->setCached('connection', new DatabaseConnection(
				$this->options['host'],
				$this->options['username'],
				$this->options['password'],
				$this->options['name'],
				array_key_exists('port', $this->options) ? $this->options['port'] : null,
				array_key_exists('socket', $this->options) ? $this->options['socket'] : null
			));
		}

		return $this->getCached('connection');
	}

	/**
	 * @return QueryBuilderWrapper
	 */
	protected function getQueryBuilderFactory()
	{

		if (!$this->isCached('queryBuilderFactory')) {
			$this->setCached('queryBuilderFactory', new QueryBuilderWrapper($this->getConnection()));
		}

		return $this->getCached('queryBuilderFactory');
	}
}
