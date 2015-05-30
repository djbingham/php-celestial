<?php
namespace Sloth\Controller;

use Sloth\Base\Controller;
use Sloth\Exception;
use Sloth\Request;

abstract class RestfulController extends Controller
{
	abstract protected function get(Request $request, $route);
	abstract protected function post(Request $request, $route);
	abstract protected function put(Request $request, $route);
	abstract protected function delete(Request $request, $route);

    public function execute(Request $request, $route)
    {
		$method = $request->method();

		if (!method_exists($this, $method)) {
			throw new Exception\InvalidRequestException(sprintf('Method not found: %s', $method));
		}

		return $this->$method($request, $route);
    }
}