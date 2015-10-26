<?php
namespace Sloth\Face;

interface RequestParserInterface
{
    /**
     * @param RequestInterface $request
     * @param string $route
     * @return ParsedRequestInterface
     */
	public function parse(RequestInterface $request, $route);
}
