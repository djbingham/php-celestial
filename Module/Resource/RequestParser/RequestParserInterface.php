<?php
namespace Sloth\Module\Resource\RequestParser;

use Sloth\App;
use Sloth\Module\Resource;
use Sloth\Request;

interface RequestParserInterface
{
	/**
	 * @param App $app
	 * @param Resource\ModuleCore $module
	 */
	public function __construct(App $app, Resource\ModuleCore $module);

    /**
     * @param Request $request
     * @param string $route
     * @return ParsedRequestInterface
     */
	public function parse(Request $request, $route);
}
