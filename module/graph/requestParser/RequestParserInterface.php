<?php
namespace Sloth\Module\Graph\RequestParser;

use Sloth\App;
use Sloth\Module\Graph;
use Sloth\Request;

interface RequestParserInterface
{
	/**
	 * @param App $app
	 * @param Graph\Factory $module
	 */
	public function __construct(App $app, Graph\Factory $module);

    /**
     * @param Request $request
     * @param string $route
     * @return ParsedRequestInterface
     */
	public function parse(Request $request, $route);
}
