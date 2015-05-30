<?php
namespace SlothDefault;

use Sloth\App;
use SlothDefault;
use SlothMySql;

class Initialisation extends \Sloth\Base\Initialisation
{
	public function getApp()
	{
		if (!isset($this->app)) {
			$this->app = new App($this->config);
		}
		return $this->app;
	}

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

	public function getRenderer()
	{
		if (!isset($this->renderer)) {
			$this->renderer = new SlothDefault\Renderer(array(
                'app' => $this->getApp(),
				'viewDirectory' => $this->config->rootDirectory() . DIRECTORY_SEPARATOR . 'view',
				'templateEngine' => new \Mustache_Autoloader()
			));
		}
		return $this->renderer;
	}
}
