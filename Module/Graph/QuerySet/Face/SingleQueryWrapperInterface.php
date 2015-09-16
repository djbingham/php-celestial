<?php
namespace Sloth\Module\Graph\QuerySet\Face;

use Sloth\Module\Graph\Definition;
use SlothMySql;

interface SingleQueryWrapperInterface extends QueryWrapperInterface
{
	/**
	 * @param SlothMySql\Face\QueryInterface $query
	 * @return $this
	 */
	public function setQuery(SlothMySql\Face\QueryInterface $query);

	/**
	 * @return SlothMySql\Face\QueryInterface
	 */
	public function getQuery();

	/**
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data);

	/**
	 * @return array
	 */
	public function getData();
}