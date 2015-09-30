<?php
namespace Module\Render\Face;

use Helper\Face\ObjectListInterface;
use Sloth\Module\Render\Face\DataProviderInterface;

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
