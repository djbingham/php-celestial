<?php
namespace Sloth\Module\Resource\Base;

use Sloth\Request;

interface RequestParser
{
    /**
     * @param Request $request
     * @param string $route
     * @return ParsedRequest
     */
	public function parse(Request $request, $route);
}
