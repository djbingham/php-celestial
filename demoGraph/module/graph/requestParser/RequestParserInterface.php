<?php
namespace DemoGraph\Module\Graph\RequestParser;

use Sloth\Request;

interface RequestParserInterface
{
    /**
     * @param Request $request
     * @param string $route
     * @return ParsedRequest
     */
	public function parse(Request $request, $route);
}
