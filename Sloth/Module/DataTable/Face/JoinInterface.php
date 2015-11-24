<?php
namespace Sloth\Module\DataTable\Face;

interface JoinInterface
{
	const MANY_TO_MANY = 'manyToMany';
	const MANY_TO_ONE = 'manyToOne';
	const ONE_TO_MANY = 'oneToMany';
	const ONE_TO_ONE = 'oneToOne';
	const ACTION_INSERT = 'insert';
	const ACTION_UPDATE = 'update';
	const ACTION_DELETE = 'delete';
	const ACTION_ASSOCIATE = 'associate';
	const ACTION_IGNORE = 'ignore';
	const ACTION_REJECT = 'reject';

	/**
	 * @return TableInterface
	 */
	public function getChildTable();

	/**
	 * @return ConstraintListInterface
	 */
	public function getConstraints();

	/**
	 * @return array
	 */
	public function getLinkedFields();
}