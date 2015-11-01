<?php
namespace Sloth\Module\DataProvider\Face;

interface DataProviderInterface
{
	/**
	 * @var array $options
	 * @return mixed
	 */
	public function getData(array $options);
}