<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\Face;

use Celestial\Module\Data\Table\Face\TableInterface;

interface QueryWrapperInterface
{
	/**
	 * @param TableInterface $table
	 * @return $this
	 */
	public function setTable(TableInterface $table);

	/**
	 * @return TableInterface
	 */
	public function getTable();

	/**
	 * @param QueryLinkInterface $parentLink
	 * @return $this
	 */
	public function setParentLink(QueryLinkInterface $parentLink);

	/**
	 * @return QueryLinkInterface
	 */
	public function getParentLink();

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
	 * @param QueryWrapperInterface
	 * @return $this
	 */
	public function setWrappedQuery(QueryWrapperInterface $wrappedQuery);

	/**
	 * @return QueryWrapperInterface
	 */
	public function getWrappedQuery();
}