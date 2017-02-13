<?php
namespace Celestial\Module\Request\Face;

interface ParsedRequestInterface extends RequestInterface
{
	public function __construct(array $properties);

	/**
	 * @return RequestInterface
	 */
	public function getOriginalRequest();
}
