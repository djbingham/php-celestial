<?php
namespace Sloth\Module\Render\Face;

use Helper\Face\ObjectListInterface;

interface DataProviderListInterface extends ObjectListInterface
{
	/**
	 * @param DataProviderInterface $provider
	 * @return $this
	 */
	public function push(DataProviderInterface $provider);

	/**
	 * @param DataProviderInterface $provider
	 * @return $this
	 */
	public function unshift(DataProviderInterface $provider);
}
