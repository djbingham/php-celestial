<?php
namespace Sloth\Module\RestApi\Face;

use Sloth\Face\RequestInterface;

interface RequestParserInterface
{
    /**
     * @param RequestInterface $request
     * @param string $route
     * @return ParsedRequestInterface
     */
	public function parse(RequestInterface $request, $route);
}
