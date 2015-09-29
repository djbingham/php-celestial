<?php
namespace Sloth\Module\Resource\RequestParser;

interface ParsedRequestInterface
{
	public function __construct(array $properties);
}
