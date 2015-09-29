<?php
namespace Sloth\Demo\Controller;

use Sloth\Module\Resource;
use SlothDemo\Helper\ResourceRequestParser;

class ResourceController extends Resource\Controller\ResourceController
{
	protected function getRequestParser()
	{
		return new ResourceRequestParser($this->app, $this->getResourceModule());
	}
}
