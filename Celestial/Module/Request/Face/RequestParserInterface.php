<?php
namespace Celestial\Module\Request\Face;

interface RequestParserInterface
{
    /**
     * @param RoutedRequestInterface $request
     * @return ParsedRequestInterface
     */
	public function parse(RoutedRequestInterface $request);
}
