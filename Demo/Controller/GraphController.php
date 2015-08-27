<?php
namespace Sloth\Demo\Controller;

use Sloth\Module\Graph;
use SlothDemo\Helper\GraphRequestParser;

class GraphController extends Graph\Controller\ResourceController
{
	protected function getRequestParser()
	{
		return new GraphRequestParser($this->app, $this->getResourceModule());
	}
}
