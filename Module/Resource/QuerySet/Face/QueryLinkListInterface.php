<?php
namespace Sloth\Module\Resource\QuerySet\Face;

use Helper\Face\ObjectListInterface;
use Sloth\Module\Resource\Definition\Table;

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
	 * @param Table $childTable
	 * @return QueryLinkInterface
	 */
	public function getByChildTable(Table $childTable);
}