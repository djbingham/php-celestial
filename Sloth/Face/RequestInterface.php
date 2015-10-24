<?php
namespace Sloth\Face;

use Sloth\Request\Params;

interface RequestInterface
{
	/**
	 * @return Boolean
	 */
	public function canBeCached();

	/**
	 * @return string
	 */
	public function getMethod();

	/**
	 * @return string
	 */
	public function getUri();

	/**
	 * @return string
	 */
	public function getPath();

	/**
	 * @return string
	 */
	public function getQueryString();

	/**
	 * @return string
	 */
	public function getFragment();

	/**
	 * @return Params
	 */
	public function getParams();

	/**
	 * @return array
	 */
	public function toArray();
}