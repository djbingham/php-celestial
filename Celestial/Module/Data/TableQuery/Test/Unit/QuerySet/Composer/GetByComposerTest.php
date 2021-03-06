<?php
namespace Celestial\Module\Data\TableQuery\Test\Unit\QuerySet\Composer;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use Celestial\Module\Data\Table\Definition;
use Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Composer\GetByComposer;
use Celestial\Module\Data\TableQuery\QuerySet\Filter\FilterParser;
use Celestial\Module\Data\TableQuery\Test\Mock\Connection;
use Celestial\Module\Data\TableQuery\Test\UnitTest;

class GetByComposerTest extends UnitTest
{
	public function testQueryComposedFromTableWithSingleTable()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		while ($table->links->length() > 0) {
			$table->links->removeByIndex(0);
		}

		$expectedQuery = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $queryWrapper */
		$queryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $queryWrapper);
		$this->assertSame($table, $queryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $queryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $queryWrapper->getChildLinks());
		$this->assertEquals(0, $queryWrapper->getChildLinks()->length());

		$query = $queryWrapper->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQueryComposedFromTableWithSingleTableUsingFilters()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		while ($table->links->length() > 0) {
			$table->links->removeByIndex(0);
		}

		// todo: Mock the filters, rather than using FilterParser
		$filters = array(
			'forename' => 'David'
		);
		$filterParser = new FilterParser();
		$filters = $filterParser->parse($table, $filters);

		$expectedQuery = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
WHERE `User`.`forename` = "David"
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $queryWrapper */
		$queryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $queryWrapper);
		$this->assertSame($table, $queryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $queryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $queryWrapper->getChildLinks());
		$this->assertEquals(0, $queryWrapper->getChildLinks()->length());

		$query = $queryWrapper->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQueryComposedFromTableWithSingleTableUsingFilterWithArrayValue()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		while ($table->links->length() > 0) {
			$table->links->removeByIndex(0);
		}

		// todo: Mock the filters, rather than using FilterParser
		$filters = array(
			'forename' => array('David', 'Flic')
		);
		$filterParser = new FilterParser();
		$filters = $filterParser->parse($table, $filters);

		$expectedQuery = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
WHERE `User`.`forename` IN ("David","Flic")
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $queryWrapper */
		$queryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $queryWrapper);
		$this->assertSame($table, $queryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $queryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $queryWrapper->getChildLinks());
		$this->assertEquals(0, $queryWrapper->getChildLinks()->length());

		$query = $queryWrapper->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQueryComposedFromTableWithOneToOneLinksAndFiltersOnLinkedTables()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'posts');

		$landlordTable = $table->links->getByName('address')
			->getChildTable()->links->getByName('landlord')->getChildTable();
		$landlordTable->links->removeByPropertyValue('name', 'friends');
		$landlordTable->links->removeByPropertyValue('name', 'posts');

		$landlord2Table = $landlordTable->links->getByName('address')
			->getChildTable()->links->getByName('landlord')->getChildTable();
		$landlord2Table->links->removeByPropertyValue('name', 'friends');
		$landlord2Table->links->removeByPropertyValue('name', 'posts');
		$landlord2Table->links->removeByPropertyValue('name', 'address');

		$filters = array(
			'forename' => 'David',
			'address' => array(
				'landlord' => array(
					'forename' => 'Mike'
				)
			)
		);

		// todo: Mock the filters, rather than using FilterParser
		$filterParser = new FilterParser();
		$filters = $filterParser->parse($table, $filters);

		$expectedQuery = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`,`User_address`.`userId` AS `User_address.userId`,`User_address`.`houseName` AS `User_address.houseName`,`User_address`.`postcode` AS `User_address.postcode`,`User_address`.`landlordId` AS `User_address.landlordId`,`User_address_landlord`.`id` AS `User_address_landlord.id`,`User_address_landlord`.`forename` AS `User_address_landlord.forename`,`User_address_landlord`.`surname` AS `User_address_landlord.surname`,`User_address_landlord_address`.`userId` AS `User_address_landlord_address.userId`,`User_address_landlord_address`.`houseName` AS `User_address_landlord_address.houseName`,`User_address_landlord_address`.`postcode` AS `User_address_landlord_address.postcode`,`User_address_landlord_address`.`landlordId` AS `User_address_landlord_address.landlordId`,`User_address_landlord_address_landlord`.`id` AS `User_address_landlord_address_landlord.id`,`User_address_landlord_address_landlord`.`forename` AS `User_address_landlord_address_landlord.forename`,`User_address_landlord_address_landlord`.`surname` AS `User_address_landlord_address_landlord.surname`
FROM `User`
LEFT JOIN `UserAddress` AS `User_address` ON (`User`.`id` = `User_address`.`userId`)
LEFT JOIN `User` AS `User_address_landlord` ON (`User_address`.`landlordId` = `User_address_landlord`.`id`)
LEFT JOIN `UserAddress` AS `User_address_landlord_address` ON (`User_address_landlord`.`id` = `User_address_landlord_address`.`userId`)
LEFT JOIN `User` AS `User_address_landlord_address_landlord` ON (`User_address_landlord_address`.`landlordId` = `User_address_landlord_address_landlord`.`id`)
WHERE `User`.`forename` = "David"
AND `User_address_landlord`.`forename` = "Mike"
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $queryWrapper */
		$queryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $queryWrapper);
		$this->assertSame($table, $queryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $queryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $queryWrapper->getChildLinks());
		$this->assertEquals(0, $queryWrapper->getChildLinks()->length());

		$query = $queryWrapper->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQuerySetComposedFromTableWithOneToOneLinkHavingMoreThanOneConstraint()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'posts');

		$addressLink = $table->links->getByName('address');
		$addressTable = $table->links->getByName('address')->getChildTable();

		$secondConstraint = new Definition\Table\Join\Constraint();
		$secondConstraint->link = $addressLink;
		$secondConstraint->parentField = $table->fields->getByName('id');
		$secondConstraint->childField = $addressTable->fields->getByName('landlordId');
		$secondConstraint->subJoins = null;
		$addressLink->constraints->push($secondConstraint);

		$addressTable->links->removeByPropertyValue('name', 'landlord');

		$expectedQuery = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`,`User_address`.`userId` AS `User_address.userId`,`User_address`.`houseName` AS `User_address.houseName`,`User_address`.`postcode` AS `User_address.postcode`,`User_address`.`landlordId` AS `User_address.landlordId`
FROM `User`
LEFT JOIN `UserAddress` AS `User_address` ON (`User`.`id` = `User_address`.`userId`
AND `User`.`id` = `User_address`.`landlordId`)
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $queryWrapper */
		$queryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $queryWrapper);
		$this->assertSame($table, $queryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $queryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $queryWrapper->getChildLinks());
		$this->assertEquals(0, $queryWrapper->getChildLinks()->length());

		$query = $queryWrapper->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQuerySetComposedFromTableWithOneToManyLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'address');

		$postTable = $table->links->getByName('posts')->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');
		$postTable->links->removeByPropertyValue('name', 'comments');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_posts`.`id` AS `User_posts.id`,`User_posts`.`authorId` AS `User_posts.authorId`,`User_posts`.`content` AS `User_posts.content`
FROM `Post` AS `User_posts`
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(2, $querySet->length());

		/** @var SingleQueryWrapperInterface $userQueryWrapper */
		$userQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $userQueryWrapper);
		$this->assertSame($table, $userQueryWrapper->getTable());

		$userLinks = $userQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $userLinks);
		$this->assertEquals(1, $userLinks->length());
		$this->assertSame($table->links->getByName('posts'), $userLinks->getByIndex(0)->getJoinDefinition());

		$userQuery = $userQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		/** @var SingleQueryWrapperInterface $postQueryWrapper */
		$postQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $postQueryWrapper);
		$this->assertSame($postTable, $postQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $postQueryWrapper->getQuery());

		$postLinks = $postQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $postLinks);
		$this->assertEquals(0, $postLinks->length());

		$postQuery = $postQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$postQuery);
	}

	public function testQuerySetComposedFromTableWithSeveralOneToManyAndOneToOneLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'address');

		$postTable = $table->links->getByName('posts')->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'comments');

		$authorTable = $postTable->links->getByName('author')->getChildTable();
		$authorTable->links->removeByPropertyValue('name', 'friends');
		$authorTable->links->removeByPropertyValue('name', 'address');

		$authorPostTable = $authorTable->links->getByName('posts')->getChildTable();
		$authorPostTable->links->removeByPropertyValue('name', 'author');
		$authorPostTable->links->removeByPropertyValue('name', 'comments');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_posts`.`id` AS `User_posts.id`,`User_posts`.`authorId` AS `User_posts.authorId`,`User_posts`.`content` AS `User_posts.content`,`User_posts_author`.`id` AS `User_posts_author.id`,`User_posts_author`.`forename` AS `User_posts_author.forename`,`User_posts_author`.`surname` AS `User_posts_author.surname`
FROM `Post` AS `User_posts`
LEFT JOIN `User` AS `User_posts_author` ON (`User_posts`.`authorId` = `User_posts_author`.`id`)
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_posts_author_posts`.`id` AS `User_posts_author_posts.id`,`User_posts_author_posts`.`authorId` AS `User_posts_author_posts.authorId`,`User_posts_author_posts`.`content` AS `User_posts_author_posts.content`
FROM `Post` AS `User_posts_author_posts`
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(3, $querySet->length());

		/** @var SingleQueryWrapperInterface $userQueryWrapper */
		$userQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $userQueryWrapper);
		$this->assertSame($table, $userQueryWrapper->getTable());

		$userLinks = $userQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $userLinks);
		$this->assertEquals(1, $userLinks->length());
		$this->assertSame($table->links->getByName('posts'), $userLinks->getByIndex(0)->getJoinDefinition());

		$userQuery = $userQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		/** @var SingleQueryWrapperInterface $postAndAuthorQueryWrapper */
		$postAndAuthorQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $postAndAuthorQueryWrapper);
		$this->assertSame($postTable, $postAndAuthorQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $postAndAuthorQueryWrapper->getQuery());

		$postAndAuthorLinks = $postAndAuthorQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $postAndAuthorLinks);
		$this->assertEquals(1, $postAndAuthorLinks->length());
		$this->assertSame($authorTable->links->getByName('posts'), $postAndAuthorLinks->getByIndex(0)->getJoinDefinition());

		$postAndAuthorQuery = $postAndAuthorQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$postAndAuthorQuery);

		/** @var SingleQueryWrapperInterface $authorPostQueryWrapper */
		$authorPostQueryWrapper = $querySet->getByIndex(2);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $authorPostQueryWrapper);
		$this->assertSame($authorPostTable, $authorPostQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $authorPostQueryWrapper->getQuery());

		$authorPostLinks = $authorPostQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $authorPostLinks);
		$this->assertEquals(0, $authorPostLinks->length());

		$authorPostLinksQuery = $authorPostQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[2], (string)$authorPostLinksQuery);
	}

	public function testQuerySetComposedFromTableWithManyToManyLink()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'address');
		$table->links->removeByPropertyValue('name', 'posts');

		$friendTable = $table->links->getByName('friends')->getChildTable();
		$friendTable->links->removeByPropertyValue('name', 'friends');
		$friendTable->links->removeByPropertyValue('name', 'address');
		$friendTable->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1` AS `User_friendLink.friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(2, $querySet->length());

		/** @var SingleQueryWrapperInterface $userQueryWrapper */
		$userQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $userQueryWrapper);
		$this->assertSame($table, $userQueryWrapper->getTable());

		$userLinks = $userQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		/** @var QueryLinkInterface $userFriendLink */
		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink->getJoinDefinition());

		$userQuery = $userQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		/** @var SingleQueryWrapperInterface $friendQueryWrapper */
		$friendQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $friendQueryWrapper);
		$this->assertSame($friendTable, $friendQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendQueryWrapper->getQuery());

		$friendLinks = $friendQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $friendLinks);
		$this->assertEquals(0, $friendLinks->length());

		$friendQuery = $friendQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);
	}

	public function testQuerySetComposedFromTableWithSeveralManyToManyLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'address');
		$table->links->removeByPropertyValue('name', 'posts');

		$friendTable = $table->links->getByName('friends')->getChildTable();
		$friendTable->links->removeByPropertyValue('name', 'address');
		$friendTable->links->removeByPropertyValue('name', 'posts');

		$friendOfFriendTable = $friendTable->links->getByName('friends')->getChildTable();
		$friendOfFriendTable->links->removeByPropertyValue('name', 'friends');
		$friendOfFriendTable->links->removeByPropertyValue('name', 'address');
		$friendOfFriendTable->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1` AS `User_friendLink.friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends_friends`.`id` AS `User_friends_friends.id`,`User_friends_friends`.`forename` AS `User_friends_friends.forename`,`User_friends_friends`.`surname` AS `User_friends_friends.surname`,`User_friends_friendLink`.`friendId1` AS `User_friends_friendLink.friendId1`
FROM `UserFriend` AS `User_friends_friendLink`
INNER JOIN `User` AS `User_friends_friends` ON (`User_friends_friendLink`.`friendId2` = `User_friends_friends`.`id`)
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table);

		$querySet = $composer->compose();

		// Test query-set data structure contains correct links between queries
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(3, $querySet->length());

		/** @var SingleQueryWrapperInterface $userQueryWrapper */
		$userQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $userQueryWrapper);
		$this->assertSame($table, $userQueryWrapper->getTable());

		$userLinks = $userQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		/** @var QueryLinkInterface $userFriendLink*/
		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink->getJoinDefinition());

		/** @var SingleQueryWrapperInterface $friendQueryWrapper */
		$friendQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $friendQueryWrapper);
		$this->assertSame($friendTable, $friendQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendQueryWrapper->getQuery());

		$friendLinks = $friendQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $friendLinks);
		$this->assertEquals(1, $friendLinks->length());

		/** @var QueryLinkInterface $friendOfFriendLink */
		$friendOfFriendLink = $friendLinks->getByIndex(0);
		$this->assertSame($friendTable->links->getByName('friends'), $friendOfFriendLink->getJoinDefinition());

		/** @var SingleQueryWrapperInterface $friendOfFriendQueryWrapper */
		$friendOfFriendQueryWrapper = $querySet->getByIndex(2);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $friendOfFriendQueryWrapper);
		$this->assertSame($friendOfFriendTable, $friendOfFriendQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendOfFriendQueryWrapper->getQuery());

		$friendOfFriendLinks = $friendOfFriendQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $friendOfFriendLinks);
		$this->assertEquals(0, $friendOfFriendLinks->length());

		// Test built query for each query-set item
		$userQuery = $userQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuery = $friendQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendQuery);
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);

		$friendOfFriendQuery = $friendOfFriendQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendOfFriendQuery);
		$this->assertEquals($expectedQueries[2], (string)$friendOfFriendQuery);
	}

	public function testQuerySetComposedFromTableWithFiltersOnManyToManyLinkedTables()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'address');
		$table->links->removeByPropertyValue('name', 'posts');

		$friendTable = $table->links->getByName('friends')->getChildTable();
		$friendTable->links->removeByPropertyValue('name', 'address');
		$friendTable->links->removeByPropertyValue('name', 'posts');

		$friendOfFriendTable = $friendTable->links->getByName('friends')->getChildTable();
		$friendOfFriendTable->links->removeByPropertyValue('name', 'friends');
		$friendOfFriendTable->links->removeByPropertyValue('name', 'address');
		$friendOfFriendTable->links->removeByPropertyValue('name', 'posts');

		// todo: Mock the filters, rather than using FilterParser
		$filters = array(
			'forename' => 'David',
			'friends' => array(
				'surname' => 'Bingham',
				'friends' => array(
					'forename' => 'Mike'
				)
			)
		);
		$filterParser = new FilterParser();
		$filters = $filterParser->parse($table, $filters);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
WHERE `User`.`forename` = "David"
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1` AS `User_friendLink.friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
WHERE `User_friends`.`surname` = "Bingham"
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends_friends`.`id` AS `User_friends_friends.id`,`User_friends_friends`.`forename` AS `User_friends_friends.forename`,`User_friends_friends`.`surname` AS `User_friends_friends.surname`,`User_friends_friendLink`.`friendId1` AS `User_friends_friendLink.friendId1`
FROM `UserFriend` AS `User_friends_friendLink`
INNER JOIN `User` AS `User_friends_friends` ON (`User_friends_friendLink`.`friendId2` = `User_friends_friends`.`id`)
WHERE `User_friends_friends`.`forename` = "Mike"
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		// Test query-set data structure contains correct links between queries
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(3, $querySet->length());

		/** @var SingleQueryWrapperInterface $userQueryWrapper */
		$userQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $userQueryWrapper);
		$this->assertSame($table, $userQueryWrapper->getTable());

		$userLinks = $userQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		/** @var QueryLinkInterface $userFriendLink */
		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink->getJoinDefinition());

		/** @var SingleQueryWrapperInterface $friendQueryWrapper */
		$friendQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $friendQueryWrapper);
		$this->assertSame($friendTable, $friendQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendQueryWrapper->getQuery());

		$friendLinks = $friendQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $friendLinks);
		$this->assertEquals(1, $friendLinks->length());

		/** @var QueryLinkInterface $friendOfFriendLink */
		$friendOfFriendLink = $friendLinks->getByIndex(0);
		$this->assertSame($friendTable->links->getByName('friends'), $friendOfFriendLink->getJoinDefinition());

		/** @var SingleQueryWrapperInterface $friendOfFriendQueryWrapper */
		$friendOfFriendQueryWrapper = $querySet->getByIndex(2);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $friendOfFriendQueryWrapper);
		$this->assertSame($friendOfFriendTable, $friendOfFriendQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendOfFriendQueryWrapper->getQuery());

		$friendOfFriendLinks = $friendOfFriendQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $friendOfFriendLinks);
		$this->assertEquals(0, $friendOfFriendLinks->length());

		// Test built query for each query-set item
		$userQuery = $userQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuery = $friendQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendQuery);
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);

		$friendOfFriendQuery = $friendOfFriendQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendOfFriendQuery);
		$this->assertEquals($expectedQueries[2], (string)$friendOfFriendQuery);
	}

	public function testQuerySetComposedFromTableWithManyToManyLinkHavingMoreThanOneConstraintOnFirstSubJoin()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'address');
		$table->links->removeByPropertyValue('name', 'posts');

		$friendLink = $table->links->getByName('friends');
		$friendTable = $friendLink->getChildTable();
		$friendSubJoins = $friendLink->constraints->getByIndex(0)->subJoins;

		$extraSubJoin = new Definition\Table\Join\SubJoin();
		$extraSubJoin->parentTable = $friendSubJoins->getByIndex(0)->parentTable;
		$extraSubJoin->parentField = clone $friendSubJoins->getByIndex(0)->parentField;
		$extraSubJoin->parentField->name = 'forename';
		$extraSubJoin->parentField->alias = 'User.forename';
		$extraSubJoin->childTable = $friendSubJoins->getByIndex(0)->childTable;
		$extraSubJoin->childField = clone $friendSubJoins->getByIndex(0)->childField;
		$extraSubJoin->childField->name = 'username1';
		$extraSubJoin->childField->alias = 'User_friendLink.username1';
		$extraSubJoin->parentJoin = $friendLink;
		$friendSubJoins->push($extraSubJoin);

		$friendTable->links->removeByPropertyValue('name', 'friends');
		$friendTable->links->removeByPropertyValue('name', 'address');
		$friendTable->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1` AS `User_friendLink.friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(2, $querySet->length());

		/** @var SingleQueryWrapperInterface $userQueryWrapper */
		$userQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $userQueryWrapper);
		$this->assertSame($table, $userQueryWrapper->getTable());

		$userLinks = $userQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		/** @var QueryLinkInterface $userFriendLink */
		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink->getJoinDefinition());

		$userQuery = $userQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		/** @var SingleQueryWrapperInterface $friendQueryWrapper */
		$friendQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $friendQueryWrapper);
		$this->assertSame($friendTable, $friendQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendQueryWrapper->getQuery());

		$friendLinks = $friendQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $friendLinks);
		$this->assertEquals(0, $friendLinks->length());

		$friendQuery = $friendQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);
	}

	public function testQuerySetComposedFromTableWithManyToManyLinkHavingMoreThanOneConstraintOnSecondSubJoin()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'address');
		$table->links->removeByPropertyValue('name', 'posts');

		$friendLink = $table->links->getByName('friends');
		$friendTable = $friendLink->getChildTable();
		$friendSubJoins = $friendLink->constraints->getByIndex(0)->subJoins;

		$extraSubJoin = new Definition\Table\Join\SubJoin();
		$extraSubJoin->parentTable = $friendSubJoins->getByIndex(1)->parentTable;
		$extraSubJoin->parentField = clone $friendSubJoins->getByIndex(1)->parentField;
		$extraSubJoin->parentField->name = 'username2';
		$extraSubJoin->parentField->alias = 'User_friendLink.username2';
		$extraSubJoin->childTable = $friendSubJoins->getByIndex(1)->childTable;
		$extraSubJoin->childField = clone $friendSubJoins->getByIndex(1)->childField;
		$extraSubJoin->childField->name = 'username';
		$extraSubJoin->childField->alias = 'User_friends.username';
		$extraSubJoin->parentJoin = $friendLink;
		$friendSubJoins->push($extraSubJoin);

		$friendTable->links->removeByPropertyValue('name', 'friends');
		$friendTable->links->removeByPropertyValue('name', 'address');
		$friendTable->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1` AS `User_friendLink.friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`
AND `User_friendLink`.`username2` = `User_friends`.`username`)
EOT;

		$composer = new GetByComposer();
		$composer->setDatabase($database)
			->setTable($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface', $querySet);
		$this->assertEquals(2, $querySet->length());

		/** @var SingleQueryWrapperInterface $userQueryWrapper */
		$userQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $userQueryWrapper);
		$this->assertSame($table, $userQueryWrapper->getTable());

		$userLinks = $userQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		/** @var QueryLinkInterface $userFriendLink */
		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink->getJoinDefinition());

		$userQuery = $userQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		/** @var SingleQueryWrapperInterface $friendQueryWrapper */
		$friendQueryWrapper = $querySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface', $friendQueryWrapper);
		$this->assertSame($friendTable, $friendQueryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Select', $friendQueryWrapper->getQuery());

		$friendLinks = $friendQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface', $friendLinks);
		$this->assertEquals(0, $friendLinks->length());

		$friendQuery = $friendQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);
	}
}
