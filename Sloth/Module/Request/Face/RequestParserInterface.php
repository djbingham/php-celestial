<?php
namespace Sloth\Module\Request\Face;

interface RequestParserInterface
{
    /**
     * @param RoutedRequestInterface $request
     * @return ParsedRequestInterface
     */
	public function parse(RoutedRequestInterface $request);
}
