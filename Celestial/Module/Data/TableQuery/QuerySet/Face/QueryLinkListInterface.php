<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\Face;

use Celestial\Helper\Face\ObjectListInterface;
use Celestial\Module\Data\Table\Face\TableInterface;

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