<?php
namespace SlothDefault;

use SlothDefault;
use SlothMySql;

class Initialisation extends \Sloth\Base\Initialisation
{
	/**
	 * @var SlothDefault\Router
	 */
	private $router;

	/**
	 * @var SlothMySql\DatabaseWrapper
	 */
	private $database;

	public function getRouter()
	{
		if (!isset($this->router)) {
			$this->router = new SlothDefault\Router($this->config);
		}
		return $this->router;
	}

	public function getDatabase()
	{
		if (!isset($this->database)) {
			$database = new SlothMySql\Connection\Database($this->config->database()->toArray());
			$connection = new SlothMySql\Connection\MySqli(
                $database->getHost(),
                $database->getUsername(),
                $database->getPassword(),
                $database->getName(),
                $database->getPort(),
                $database->getSocket()
            );
			$queryBuilder = new SlothMySql\QueryBuilder\Wrapper($connection);
			$this->database = new SlothMySql\DatabaseWrapper($connection, $queryBuilder);
		}
		return $this->database;
	}
}
