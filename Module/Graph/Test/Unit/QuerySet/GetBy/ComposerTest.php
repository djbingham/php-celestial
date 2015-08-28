<?php
namespace Sloth\Module\Graph\Test\Unit\QuerySet\GetBy;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use Sloth\Module\Graph\QuerySet\GetBy\Composer;
use Sloth\Module\Graph\QuerySet\FilterParser;
use Sloth\Module\Graph\Definition;
use Sloth\Module\Graph\Test\Mock\Connection;
use DemoGraph\Test\UnitTest;

class ComposerTest extends UnitTest
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

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'tableName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
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

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'tableName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
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

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'tableName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
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
INNER JOIN `UserAddress` AS `User_address` ON (`User`.`id` = `User_address`.`userId`)
INNER JOIN `User` AS `User_address_landlord` ON (`User_address`.`landlordId` = `User_address_landlord`.`id`)
INNER JOIN `UserAddress` AS `User_address_landlord_address` ON (`User_address_landlord`.`id` = `User_address_landlord_address`.`userId`)
INNER JOIN `User` AS `User_address_landlord_address_landlord` ON (`User_address_landlord_address`.`landlordId` = `User_address_landlord_address_landlord`.`id`)
WHERE `User`.`forename` = "David"
AND `User_address_landlord`.`forename` = "Mike"
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'tableName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
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
INNER JOIN `UserAddress` AS `User_address` ON (`User`.`id` = `User_address`.`userId`
AND `User`.`id` = `User_address`.`landlordId`)
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'tableName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
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

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_posts`.`id` AS `User_posts.id`,`User_posts`.`authorId` AS `User_posts.authorId`,`User_posts`.`content` AS `User_posts.content`
FROM `Post` AS `User_posts`
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(2, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'tableName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $userLinks);
		$this->assertEquals(1, $userLinks->length());
		$this->assertSame($table->links->getByName('posts'), $userLinks->getByIndex(0));

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$postQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $postQuerySetItem);
		$this->assertAttributeEquals('User_posts', 'tableName', $postQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $postQuerySetItem->getQuery());

		$postLinks = $postQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $postLinks);
		$this->assertEquals(0, $postLinks->length());

		$postQuery = $postQuerySetItem->getQuery();
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
		$authorTable = $postTable->links->getByName('author')->getChildTable();
		$authorTable->links->removeByPropertyValue('name', 'friends');
		$authorTable->links->removeByPropertyValue('name', 'address');

		$authorPostTable = $authorTable->links->getByName('posts')->getChildTable();
		$authorPostTable->links->removeByPropertyValue('name', 'author');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_posts`.`id` AS `User_posts.id`,`User_posts`.`authorId` AS `User_posts.authorId`,`User_posts`.`content` AS `User_posts.content`,`User_posts_author`.`id` AS `User_posts_author.id`,`User_posts_author`.`forename` AS `User_posts_author.forename`,`User_posts_author`.`surname` AS `User_posts_author.surname`
FROM `Post` AS `User_posts`
INNER JOIN `User` AS `User_posts_author` ON (`User_posts`.`authorId` = `User_posts_author`.`id`)
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_posts_author_posts`.`id` AS `User_posts_author_posts.id`,`User_posts_author_posts`.`authorId` AS `User_posts_author_posts.authorId`,`User_posts_author_posts`.`content` AS `User_posts_author_posts.content`
FROM `Post` AS `User_posts_author_posts`
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(3, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'tableName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $userLinks);
		$this->assertEquals(1, $userLinks->length());
		$this->assertSame($table->links->getByName('posts'), $userLinks->getByIndex(0));

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$postAndAuthorQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $postAndAuthorQuerySetItem);
		$this->assertAttributeEquals('User_posts', 'tableName', $postAndAuthorQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $postAndAuthorQuerySetItem->getQuery());

		$postAndAuthorLinks = $postAndAuthorQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $postAndAuthorLinks);
		$this->assertEquals(1, $postAndAuthorLinks->length());
		$this->assertSame($authorTable->links->getByName('posts'), $postAndAuthorLinks->getByIndex(0));

		$postAndAuthorQuery = $postAndAuthorQuerySetItem->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$postAndAuthorQuery);

		$authorPostQuerySetItem = $querySet->getByIndex(2);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $authorPostQuerySetItem);
		$this->assertAttributeEquals('User_posts_author_posts', 'tableName', $authorPostQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $authorPostQuerySetItem->getQuery());

		$authorPostLinks = $authorPostQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $authorPostLinks);
		$this->assertEquals(0, $authorPostLinks->length());

		$authorPostLinksQuery = $authorPostQuerySetItem->getQuery();
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

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(2, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'tableName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink);

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'tableName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $friendLinks);
		$this->assertEquals(0, $friendLinks->length());

		$friendQuery = $friendQuerySetItem->getQuery();
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

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table);

		$querySet = $composer->compose();

		// Test query-set data structure contains correct links between queries
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(3, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'tableName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'tableName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $friendLinks);
		$this->assertEquals(1, $friendLinks->length());

		$friendOfFriendLink = $friendLinks->getByIndex(0);
		$this->assertSame($friendTable->links->getByName('friends'), $friendOfFriendLink);

		$friendOfFriendQuerySetItem = $querySet->getByIndex(2);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $friendOfFriendQuerySetItem);
		$this->assertAttributeEquals('User_friends_friends', 'tableName', $friendOfFriendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendOfFriendQuerySetItem->getQuery());

		$friendOfFriendLinks = $friendOfFriendQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $friendOfFriendLinks);
		$this->assertEquals(0, $friendOfFriendLinks->length());

		// Test built query for each query-set item
		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuery = $friendQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuery);
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);

		$friendOfFriendQuery = $friendOfFriendQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendOfFriendQuery);
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

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table)
			->setFilters($filters);

		$querySet = $composer->compose();

		// Test query-set data structure contains correct links between queries
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(3, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'tableName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'tableName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $friendLinks);
		$this->assertEquals(1, $friendLinks->length());

		$friendOfFriendLink = $friendLinks->getByIndex(0);
		$this->assertSame($friendTable->links->getByName('friends'), $friendOfFriendLink);

		$friendOfFriendQuerySetItem = $querySet->getByIndex(2);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $friendOfFriendQuerySetItem);
		$this->assertAttributeEquals('User_friends_friends', 'tableName', $friendOfFriendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendOfFriendQuerySetItem->getQuery());

		$friendOfFriendLinks = $friendOfFriendQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $friendOfFriendLinks);
		$this->assertEquals(0, $friendOfFriendLinks->length());

		// Test built query for each query-set item
		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuery = $friendQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuery);
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);

		$friendOfFriendQuery = $friendOfFriendQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendOfFriendQuery);
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

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(2, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'tableName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink);

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'tableName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $friendLinks);
		$this->assertEquals(0, $friendLinks->length());

		$friendQuery = $friendQuerySetItem->getQuery();
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

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($table);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(2, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'tableName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($table->links->getByName('friends'), $userFriendLink);

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'tableName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\Definition\Table\JoinList', $friendLinks);
		$this->assertEquals(0, $friendLinks->length());

		$friendQuery = $friendQuerySetItem->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);
	}
}
