<?php
namespace Sloth\Module\Graph\QuerySet\Face;

use Sloth\Module\Graph\Definition\Table;

interface QueryWrapperInterface
{
	/**
	 * @param Table $table
	 * @return $this
	 */
	public function setTable(Table $table);

	/**
	 * @return Table
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