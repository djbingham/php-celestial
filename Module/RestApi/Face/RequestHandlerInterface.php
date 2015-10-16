<?php
namespace Sloth\Module\RestApi\Face;

interface RequestHandlerInterface
{
	public function handle(ParsedRequestInterface $parsedRequest, $route);
}
