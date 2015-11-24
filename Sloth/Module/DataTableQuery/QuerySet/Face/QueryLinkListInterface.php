<?php
namespace Sloth\Module\DataTableQuery\QuerySet\Face;

use Helper\Face\ObjectListInterface;
use Sloth\Module\DataTable\Face\TableInterface;

interface QueryLinkListInterface extends ObjectListInterface
{
	/**
	 * @param QueryWrapperInterface $parentQueryWrapper
	 * @return $this
	 */
	public function setParentQueryWrapper(QueryWrapperInterface $parentQueryWrapper);

	/**
	 * @return QueryWrapperInterface
	 */
	public function getParentQueryWrapper();

	/**
	 * @param QueryLinkListInterface $childLinks
	 * @return $this
	 */
	public function setChildLinks(QueryLinkListInterface $childLinks);

	/**
	 * @return QueryLinkListInterface
	 */
	public function getChildLinks();

	/**
	 * @param TableInterface $childTable
	 * @return QueryLinkInterface
	 */
	public function getByChildTable(TableInterface $childTable);
}