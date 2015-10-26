<?php
namespace Sloth\Face;

interface ParsedRequestInterface extends RequestInterface
{
	public function __construct(array $properties);

	/**
	 * @return RequestInterface
	 */
	public function getOriginalRequest();
}
