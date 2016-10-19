<?php
namespace Sloth\Module\Data\TableQuery\Test\Unit\QuerySet\Composer;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use Sloth\Module\Data\Table\Definition;
use Sloth\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Composer\DeleteComposer;
use Sloth\Module\Data\TableQuery\Test\Mock\Connection;
use Sloth\Module\Data\TableQuery\Test\UnitTest;

class DeleteComposerTest extends UnitTest
{
	public function testQuerySetComposedFromSingleTable()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		while ($table->links->length() > 0) {
			$table->links->removeByIndex(0);
		}

		$filters = array(
			'id' => 1
		);

		$expectedQuery = <<<EOT
DELETE FROM `User`
WHERE `User`.`id` = 1
EOT;

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $queryWrapper */
		$queryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $queryWrapper);
		$this->assertSame($table, $queryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $queryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $queryWrapper->getChildLinks());
		$this->assertEquals(0, $queryWrapper->getChildLinks()->length());

		$query = $queryWrapper->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQuerySetRejectedIfNoFiltersGiven()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		while ($table->links->length() > 0) {
			$table->links->removeByIndex(0);
		}

		$filters = array();

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$this->setExpectedException(
			'Sloth\Exception\InvalidRequestException',
			'No filters given for table: User'
		);
		$composer->compose();
	}

	public function testQuerySetComposedFromTablesWithOneToOneLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'posts');

		$addressTable = $table->links->getByName('address')->getChildTable();
		$addressTable->links->removeByPropertyValue('name', 'landlord');

		$filters = array(
			'id' => 7,
			'address' => array(
				'userId' => 12
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
DELETE FROM `User`
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `UserAddress`
WHERE `UserAddress`.`userId` = 12
EOT;

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(2, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(0, $firstQueryWrapper->getChildLinks()->length());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $secondQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertSame($addressTable, $secondQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());
	}

	public function testQuerySetComposedFromTablesWithIgnoredLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'posts');

		$addressTable = $table->links->getByName('address')->getChildTable();
		$landlordTable = $addressTable->links->getByName('landlord')->getChildTable();
		$landlordAddressTable = $landlordTable->links->getByName('address')->getChildTable();
		$landlord2Table = $landlordAddressTable->links->getByName('landlord')->getChildTable();

		$landlordTable->links->removeByPropertyValue('name', 'friends');
		$landlordTable->links->removeByPropertyValue('name', 'posts');

		$landlord2Table->links->removeByPropertyValue('name', 'friends');
		$landlord2Table->links->removeByPropertyValue('name', 'posts');
		$landlord2Table->links->removeByPropertyValue('name', 'address');

		$filters = array(
			'id' => 7,
			'address' => array(
				'userId' => 12
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
DELETE FROM `User`
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `UserAddress`
WHERE `UserAddress`.`userId` = 12
EOT;

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(2, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(0, $firstQueryWrapper->getChildLinks()->length());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $secondQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertSame($addressTable, $secondQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());
	}

	public function testQuerySetRejectedIfOneToOneLinkSetToAssociateOnDelete()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'posts');

		$addressTable = $table->links->getByName('address')->getChildTable();
		while ($addressTable->links->length() > 0) {
			$addressTable->links->removeByIndex(0);
		}

		$table->links->getByName('address')->onDelete = Definition\Table\Join::ACTION_ASSOCIATE;

		$filters = array(
			'id' => 1,
			'address' => array(
				'userId' => 1
			)
		);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$this->setExpectedException('Sloth\Exception\InvalidRequestException', 'On delete action should not be "associate" for a join that is not many-to-many: User_address');
		$composer->compose();
	}

	public function testQuerySetRejectedIfFiltersContainsDisallowedSubTableFields()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'posts');

		$addressTable = $table->links->getByName('address')->getChildTable();
		$landlordTable = $addressTable->links->getByName('landlord')->getChildTable();
		$landlordAddressTable = $landlordTable->links->getByName('address')->getChildTable();
		$landlord2Table = $landlordAddressTable->links->getByName('landlord')->getChildTable();

		$landlordTable->links->removeByPropertyValue('name', 'friends');
		$landlordTable->links->removeByPropertyValue('name', 'posts');

		$landlord2Table->links->removeByPropertyValue('name', 'friends');
		$landlord2Table->links->removeByPropertyValue('name', 'posts');
		$landlord2Table->links->removeByPropertyValue('name', 'address');

		$addressTable->links->getByName('landlord')->onDelete = Definition\Table\Join::ACTION_REJECT;

		$filters = array(
			'id' => 1,
			'address' => array(
				'userId' => 1,
				'landlord' => array(
					'id' => 1
				)
			)
		);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$this->setExpectedException(
			'Sloth\Exception\InvalidRequestException',
			'Data to delete includes a disallowed subset: User_address_landlord'
		);
		$composer->compose();
	}

	public function testQuerySetRejectedIfNoFiltersGivenForSubTable()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'posts');

		$addressTable = $table->links->getByName('address')->getChildTable();
		while ($addressTable->links->length() > 0) {
			$addressTable->links->removeByIndex(0);
		}

		$filters = array(
			'id' => 1,
			'address' => array(

			)
		);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$this->setExpectedException(
			'Sloth\Exception\InvalidRequestException',
			'No filters given for table: User_address'
		);
		$composer->compose();
	}

	public function testQuerySetComposedFromTableWithOneToManyLinkData()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'address');

		$linkToPosts = $table->links->getByName('posts');
		$linkToPosts->onDelete = Definition\Table\Join::ACTION_DELETE;

		$postTable = $linkToPosts->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');
		$postTable->links->removeByPropertyValue('name', 'comments');

		$filters = array(
			'id' => 7,
			'posts' => array(
				array(
					'id' => 12
				),
				array(
					'id' => 13
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
DELETE FROM `User`
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `Post`
WHERE `Post`.`id` = 12
OR `Post`.`id` = 13
EOT;

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(2, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(0, $firstQueryWrapper->getChildLinks()->length());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());
		$this->assertSame($postTable, $secondQueryWrapper->getTable());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $secondQuery);
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());
	}

	public function testQuerySetComposedFromTableWithSeveralLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');

		$addressTable = $table->links->getByName('address')->getChildTable();
		$addressTable->links->removeByPropertyValue('name', 'landlord');

		$linkToPosts = $table->links->getByName('posts');
		$linkToPosts->onDelete = Definition\Table\Join::ACTION_DELETE;

		$postTable = $linkToPosts->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');
		$postTable->links->removeByPropertyValue('name', 'comments');

		$filters = array(
			'id' => 7,
			'address' => array(
				'userId' => 7
			),
			'posts' => array(
				array(
					'id' => 12
				),
				array(
					'id' => 13
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
DELETE FROM `User`
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `UserAddress`
WHERE `UserAddress`.`userId` = 7
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `Post`
WHERE `Post`.`id` = 12
OR `Post`.`id` = 13
EOT;

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(3, $querySet->length());

		/** @var SingleQueryWrapperInterface $userDeleteQueryWrapper */
		$userDeleteQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $userDeleteQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $userDeleteQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $userDeleteQueryWrapper->getChildLinks());

		$userDeleteQuery = $userDeleteQueryWrapper->getQuery();
		$this->assertSame($table, $userDeleteQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$userDeleteQuery);
		$this->assertEquals(0, $userDeleteQueryWrapper->getChildLinks()->length());

		/** @var SingleQueryWrapperInterface $addressDeleteQueryWrapper */
		$addressDeleteQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $addressDeleteQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $addressDeleteQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $addressDeleteQueryWrapper->getChildLinks());

		/** @var SingleQueryWrapperInterface $postDeleteQueryWrapper */
		$postDeleteQueryWrapper = $querySet->getByIndex(2);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $postDeleteQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $postDeleteQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $postDeleteQueryWrapper->getChildLinks());

		$addressDeleteQuery = $addressDeleteQueryWrapper->getQuery();
		$this->assertSame($addressTable, $addressDeleteQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$addressDeleteQuery);
		$this->assertEquals(0, $addressDeleteQueryWrapper->getChildLinks()->length());

		$firstPostDeleteQuery = $postDeleteQueryWrapper->getQuery();
		$this->assertSame($postTable, $postDeleteQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[2], (string)$firstPostDeleteQuery);
		$this->assertEquals(0, $postDeleteQueryWrapper->getChildLinks()->length());
	}

	public function testQuerySetComposedFromTableWithChainedOneToManyLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'address');
		$table->links->removeByPropertyValue('name', 'friends');

		$linkToPosts = $table->links->getByName('posts');
		$linkToPosts->onDelete = Definition\Table\Join::ACTION_DELETE;

		$postTable = $linkToPosts->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');

		$linkToComments = $postTable->links->getByName('comments');
		$linkToComments->onDelete = Definition\Table\Join::ACTION_DELETE;

		$commentTable = $linkToComments->getChildTable();
		$commentTable->links->removeByPropertyValue('name', 'author');
		$commentTable->links->removeByPropertyValue('name', 'post');
		$commentTable->links->removeByPropertyValue('name', 'replies');

		$filters = array(
			'id' => 1,
			'posts' => array(
				array(
					'id' => 11,
					'comments' => array(
						array(
							'id' => 21
						),
						array(
							'id' => 22
						)
					)
				),
				array(
					'id' => 12
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
DELETE FROM `User`
WHERE `User`.`id` = 1
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `Post`
WHERE `Post`.`id` = 11
OR `Post`.`id` = 12
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `Comment`
WHERE `Comment`.`id` = 21
OR `Comment`.`id` = 22
EOT;

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(3, $querySet->length());
		$this->assertNull($querySet->getChildLinks());

		/** @var SingleQueryWrapperInterface $deleteUserQueryWrapper */
		$deleteUserQueryWrapper = $querySet->getbyIndex(0);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $deleteUserQueryWrapper->getQuery());
		$this->assertSame($table, $deleteUserQueryWrapper->getTable());

		/** @var SingleQueryWrapperInterface $deletePostsQueryWrapper */
		$deletePostsQueryWrapper = $querySet->getbyIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $deletePostsQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $deletePostsQueryWrapper->getQuery());

		$deletePostsChildLinks = $deletePostsQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $deletePostsChildLinks);
		$this->assertSame($postTable, $deletePostsQueryWrapper->getTable());
		$this->assertEquals(0, $deletePostsChildLinks->length());

		/** @var SingleQueryWrapperInterface $deleteCommentsQueryWrapper */
		$deleteCommentsQueryWrapper = $querySet->getbyIndex(2);
		$deleteCommentsChildLinks = $deleteCommentsQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $deleteCommentsQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $deleteCommentsQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $deleteCommentsChildLinks);
		$this->assertSame($commentTable, $deleteCommentsQueryWrapper->getTable());
		$this->assertEquals(0, $deleteCommentsChildLinks->length());

		$deleteUserQuery = $deleteUserQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[0], (string)$deleteUserQuery);

		$deletePostsQuery = $deletePostsQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$deletePostsQuery);

		$deleteCommentsQuery = $deleteCommentsQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[2], (string)$deleteCommentsQuery);
	}

	public function testQuerySetComposedFromTableWithManyToManyLinkSetToAssociateOnDelete()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'address');
		$table->links->removeByPropertyValue('name', 'posts');

		$friendLink = $table->links->getByName('friends');
		$friendTable = $friendLink->getChildTable();
		$friendTable->links->removeByPropertyValue('name', 'posts');
		$friendTable->links->removeByPropertyValue('name', 'address');
		$friendTable->links->removeByPropertyValue('name', 'friends');

		$filters = array(
			'id' => 1,
			'friends' => array(
				array(
					'id' => 2
				),
				array(
					'id' => 3
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
DELETE FROM `User`
WHERE `User`.`id` = 1
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `UserFriend`
WHERE `UserFriend`.`friendId1` = 1
EOT;

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(2, $querySet->length());

		/** @var SingleQueryWrapperInterface $deleteUserQueryWrapper */
		$deleteUserQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $deleteUserQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $deleteUserQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $deleteUserQueryWrapper->getChildLinks());

		$deleteUserQuery = $deleteUserQueryWrapper->getQuery();
		$this->assertSame($table, $deleteUserQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$deleteUserQuery);
		$this->assertEquals(0, $deleteUserQueryWrapper->getChildLinks()->length());

		/** @var SingleQueryWrapperInterface $deleteFriendsQueryWrapper */
		$deleteFriendsQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $deleteFriendsQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Delete', $deleteFriendsQueryWrapper->getQuery());
		$this->assertEquals(0, $deleteFriendsQueryWrapper->getChildLinks()->length());

		$deleteFriendsQuery = $deleteFriendsQueryWrapper->getQuery();
		$this->assertSame($friendLink->intermediaryTables->getByIndex(0), $deleteFriendsQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$deleteFriendsQuery);
		$this->assertEquals(0, $deleteFriendsQueryWrapper->getChildLinks()->length());
	}
}
