<?php
namespace Sloth\SlothDefault;

use Sloth\SlothDefault;
use SlothMySql;

class Initialisation extends \Sloth\Base\Initialisation
{
	/**
	 * @var SlothDefault\Router
	 */
	private $router;

	public function getRouter()
	{
		if (!isset($this->router)) {
			$this->router = new SlothDefault\Router($this->config);
		}
		return $this->router;
	}
}
