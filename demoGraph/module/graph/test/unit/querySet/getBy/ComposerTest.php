<?php
namespace DemoGraph\Module\Graph\Test\QueryBuilder;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use DemoGraph\Module\Graph\QuerySet\GetBy\Composer;
use DemoGraph\Module\Graph\QuerySet\FilterParser;
use DemoGraph\Module\Graph\ResourceDefinition;
use DemoGraph\Module\Graph\Test\Mock\Connection;
use DemoGraph\Test\UnitTest;

class ComposerTest extends UnitTest
{
	public function testQueryComposedFromResourceWithSingleTable()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		while ($resource->links->length() > 0) {
			$resource->links->removeByIndex(0);
		}

		$expectedQuery = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQueryComposedFromResourceWithSingleTableUsingFilters()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		while ($resource->links->length() > 0) {
			$resource->links->removeByIndex(0);
		}

		// todo: Mock the filters, rather than using FilterParser
		$filters = array(
			'forename' => 'David'
		);
		$filterParser = new FilterParser();
		$filters = $filterParser->parse($resource, $filters);

		$expectedQuery = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
WHERE `User`.`forename` = "David"
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQueryComposedFromResourceWithSingleTableUsingFilterWithArrayValue()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		while ($resource->links->length() > 0) {
			$resource->links->removeByIndex(0);
		}

		// todo: Mock the filters, rather than using FilterParser
		$filters = array(
			'forename' => array('David', 'Flic')
		);
		$filterParser = new FilterParser();
		$filters = $filterParser->parse($resource, $filters);

		$expectedQuery = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
WHERE `User`.`forename` IN ("David","Flic")
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQueryComposedFromResourceWithOneToOneLinksAndFiltersOnLinkedTables()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'friends');
		$resource->links->removeByPropertyValue('name', 'posts');

		$landlordResource = $resource->links->getByName('address')
			->getChildResource()->links->getByName('landlord')->getChildResource();
		$landlordResource->links->removeByPropertyValue('name', 'friends');
		$landlordResource->links->removeByPropertyValue('name', 'posts');

		$landlord2Resource = $landlordResource->links->getByName('address')
			->getChildResource()->links->getByName('landlord')->getChildResource();
		$landlord2Resource->links->removeByPropertyValue('name', 'friends');
		$landlord2Resource->links->removeByPropertyValue('name', 'posts');
		$landlord2Resource->links->removeByPropertyValue('name', 'address');

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
		$filters = $filterParser->parse($resource, $filters);

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
			->setResource($resource)
			->setFilters($filters);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQuerySetComposedFromResourceWithOneToOneLinkHavingMoreThanOneConstraint()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'friends');
		$resource->links->removeByPropertyValue('name', 'posts');

		$addressLink = $resource->links->getByName('address');
		$addressResource = $resource->links->getByName('address')->getChildResource();

		$secondConstraint = new ResourceDefinition\LinkConstraint();
		$secondConstraint->link = $addressLink;
		$secondConstraint->parentAttribute = $resource->attributes->getByName('id');
		$secondConstraint->childAttribute = $addressResource->attributes->getByName('landlordId');
		$secondConstraint->subJoins = null;
		$addressLink->constraints->push($secondConstraint);

		$addressResource->links->removeByPropertyValue('name', 'landlord');

		$expectedQuery = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`,`User_address`.`userId` AS `User_address.userId`,`User_address`.`houseName` AS `User_address.houseName`,`User_address`.`postcode` AS `User_address.postcode`,`User_address`.`landlordId` AS `User_address.landlordId`
FROM `User`
INNER JOIN `UserAddress` AS `User_address` ON (`User`.`id` = `User_address`.`userId`
AND `User`.`id` = `User_address`.`landlordId`)
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(1, $querySet->length());

		$querySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $querySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $querySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $querySetItem->getQuery());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $querySetItem->getLinks());
		$this->assertEquals(0, $querySetItem->getLinks()->length());

		$query = $querySetItem->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQuerySetComposedFromResourceWithOneToManyLinks()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'friends');
		$resource->links->removeByPropertyValue('name', 'address');

		$postResource = $resource->links->getByName('posts')->getChildResource();
		$postResource->links->removeByPropertyValue('name', 'author');

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
			->setResource($resource);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(2, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $userLinks);
		$this->assertEquals(1, $userLinks->length());
		$this->assertSame($resource->links->getByName('posts'), $userLinks->getByIndex(0));

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$postQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $postQuerySetItem);
		$this->assertAttributeEquals('User_posts', 'resourceName', $postQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $postQuerySetItem->getQuery());

		$postLinks = $postQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $postLinks);
		$this->assertEquals(0, $postLinks->length());

		$postQuery = $postQuerySetItem->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$postQuery);
	}

	public function testQuerySetComposedFromResourceWithSeveralOneToManyAndOneToOneLinks()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'friends');
		$resource->links->removeByPropertyValue('name', 'address');

		$postResource = $resource->links->getByName('posts')->getChildResource();
		$authorResource = $postResource->links->getByName('author')->getChildResource();
		$authorResource->links->removeByPropertyValue('name', 'friends');
		$authorResource->links->removeByPropertyValue('name', 'address');

		$authorPostResource = $authorResource->links->getByName('posts')->getChildResource();
		$authorPostResource->links->removeByPropertyValue('name', 'author');

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
			->setResource($resource);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(3, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $userLinks);
		$this->assertEquals(1, $userLinks->length());
		$this->assertSame($resource->links->getByName('posts'), $userLinks->getByIndex(0));

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$postAndAuthorQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $postAndAuthorQuerySetItem);
		$this->assertAttributeEquals('User_posts', 'resourceName', $postAndAuthorQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $postAndAuthorQuerySetItem->getQuery());

		$postAndAuthorLinks = $postAndAuthorQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $postAndAuthorLinks);
		$this->assertEquals(1, $postAndAuthorLinks->length());
		$this->assertSame($authorResource->links->getByName('posts'), $postAndAuthorLinks->getByIndex(0));

		$postAndAuthorQuery = $postAndAuthorQuerySetItem->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$postAndAuthorQuery);

		$authorPostQuerySetItem = $querySet->getByIndex(2);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $authorPostQuerySetItem);
		$this->assertAttributeEquals('User_posts_author_posts', 'resourceName', $authorPostQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $authorPostQuerySetItem->getQuery());

		$authorPostLinks = $authorPostQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $authorPostLinks);
		$this->assertEquals(0, $authorPostLinks->length());

		$authorPostLinksQuery = $authorPostQuerySetItem->getQuery();
		$this->assertEquals($expectedQueries[2], (string)$authorPostLinksQuery);
	}

	public function testQuerySetComposedFromResourceWithManyToManyLink()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'address');
		$resource->links->removeByPropertyValue('name', 'posts');

		$friendResource = $resource->links->getByName('friends')->getChildResource();
		$friendResource->links->removeByPropertyValue('name', 'friends');
		$friendResource->links->removeByPropertyValue('name', 'address');
		$friendResource->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(2, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($resource->links->getByName('friends'), $userFriendLink);

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'resourceName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $friendLinks);
		$this->assertEquals(0, $friendLinks->length());

		$friendQuery = $friendQuerySetItem->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);
	}

	public function testQuerySetComposedFromResourceWithSeveralManyToManyLinks()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'address');
		$resource->links->removeByPropertyValue('name', 'posts');

		$friendResource = $resource->links->getByName('friends')->getChildResource();
		$friendResource->links->removeByPropertyValue('name', 'address');
		$friendResource->links->removeByPropertyValue('name', 'posts');

		$friendOfFriendResource = $friendResource->links->getByName('friends')->getChildResource();
		$friendOfFriendResource->links->removeByPropertyValue('name', 'friends');
		$friendOfFriendResource->links->removeByPropertyValue('name', 'address');
		$friendOfFriendResource->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends_friends`.`id` AS `User_friends_friends.id`,`User_friends_friends`.`forename` AS `User_friends_friends.forename`,`User_friends_friends`.`surname` AS `User_friends_friends.surname`,`User_friends_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friends_friendLink`
INNER JOIN `User` AS `User_friends_friends` ON (`User_friends_friendLink`.`friendId2` = `User_friends_friends`.`id`)
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		// Test query-set data structure contains correct links between queries
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(3, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($resource->links->getByName('friends'), $userFriendLink);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'resourceName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $friendLinks);
		$this->assertEquals(1, $friendLinks->length());

		$friendOfFriendLink = $friendLinks->getByIndex(0);
		$this->assertSame($friendResource->links->getByName('friends'), $friendOfFriendLink);

		$friendOfFriendQuerySetItem = $querySet->getByIndex(2);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $friendOfFriendQuerySetItem);
		$this->assertAttributeEquals('User_friends_friends', 'resourceName', $friendOfFriendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendOfFriendQuerySetItem->getQuery());

		$friendOfFriendLinks = $friendOfFriendQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $friendOfFriendLinks);
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

	public function testQuerySetComposedFromResourceWithFiltersOnManyToManyLinkedTables()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'address');
		$resource->links->removeByPropertyValue('name', 'posts');

		$friendResource = $resource->links->getByName('friends')->getChildResource();
		$friendResource->links->removeByPropertyValue('name', 'address');
		$friendResource->links->removeByPropertyValue('name', 'posts');

		$friendOfFriendResource = $friendResource->links->getByName('friends')->getChildResource();
		$friendOfFriendResource->links->removeByPropertyValue('name', 'friends');
		$friendOfFriendResource->links->removeByPropertyValue('name', 'address');
		$friendOfFriendResource->links->removeByPropertyValue('name', 'posts');

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
		$filters = $filterParser->parse($resource, $filters);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
WHERE `User`.`forename` = "David"
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
WHERE `User_friends`.`surname` = "Bingham"
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends_friends`.`id` AS `User_friends_friends.id`,`User_friends_friends`.`forename` AS `User_friends_friends.forename`,`User_friends_friends`.`surname` AS `User_friends_friends.surname`,`User_friends_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friends_friendLink`
INNER JOIN `User` AS `User_friends_friends` ON (`User_friends_friendLink`.`friendId2` = `User_friends_friends`.`id`)
WHERE `User_friends_friends`.`forename` = "Mike"
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource)
			->setFilters($filters);

		$querySet = $composer->compose();

		// Test query-set data structure contains correct links between queries
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(3, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($resource->links->getByName('friends'), $userFriendLink);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'resourceName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $friendLinks);
		$this->assertEquals(1, $friendLinks->length());

		$friendOfFriendLink = $friendLinks->getByIndex(0);
		$this->assertSame($friendResource->links->getByName('friends'), $friendOfFriendLink);

		$friendOfFriendQuerySetItem = $querySet->getByIndex(2);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $friendOfFriendQuerySetItem);
		$this->assertAttributeEquals('User_friends_friends', 'resourceName', $friendOfFriendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendOfFriendQuerySetItem->getQuery());

		$friendOfFriendLinks = $friendOfFriendQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $friendOfFriendLinks);
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

	public function testQuerySetComposedFromResourceWithManyToManyLinkHavingMoreThanOneConstraintOnFirstSubJoin()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'address');
		$resource->links->removeByPropertyValue('name', 'posts');

		$friendLink = $resource->links->getByName('friends');
		$friendResource = $friendLink->getChildResource();
		$friendSubJoins = $friendLink->constraints->getByIndex(0)->subJoins;

		$extraSubJoin = new ResourceDefinition\LinkSubJoin();
		$extraSubJoin->parentResource = $friendSubJoins->getByIndex(0)->parentResource;
		$extraSubJoin->parentAttribute = clone $friendSubJoins->getByIndex(0)->parentAttribute;
		$extraSubJoin->parentAttribute->name = 'forename';
		$extraSubJoin->parentAttribute->alias = 'User.forename';
		$extraSubJoin->childResource = $friendSubJoins->getByIndex(0)->childResource;
		$extraSubJoin->childAttribute = clone $friendSubJoins->getByIndex(0)->childAttribute;
		$extraSubJoin->childAttribute->name = 'username1';
		$extraSubJoin->childAttribute->alias = 'User_friendLink.username1';
		$extraSubJoin->parentJoin = $friendLink;
		$friendSubJoins->push($extraSubJoin);

		$friendResource->links->removeByPropertyValue('name', 'friends');
		$friendResource->links->removeByPropertyValue('name', 'address');
		$friendResource->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(2, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($resource->links->getByName('friends'), $userFriendLink);

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'resourceName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $friendLinks);
		$this->assertEquals(0, $friendLinks->length());

		$friendQuery = $friendQuerySetItem->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);
	}

	public function testQuerySetComposedFromResourceWithManyToManyLinkHavingMoreThanOneConstraintOnSecondSubJoin()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'address');
		$resource->links->removeByPropertyValue('name', 'posts');

		$friendLink = $resource->links->getByName('friends');
		$friendResource = $friendLink->getChildResource();
		$friendSubJoins = $friendLink->constraints->getByIndex(0)->subJoins;

		$extraSubJoin = new ResourceDefinition\LinkSubJoin();
		$extraSubJoin->parentResource = $friendSubJoins->getByIndex(1)->parentResource;
		$extraSubJoin->parentAttribute = clone $friendSubJoins->getByIndex(1)->parentAttribute;
		$extraSubJoin->parentAttribute->name = 'username2';
		$extraSubJoin->parentAttribute->alias = 'User_friendLink.username2';
		$extraSubJoin->childResource = $friendSubJoins->getByIndex(1)->childResource;
		$extraSubJoin->childAttribute = clone $friendSubJoins->getByIndex(1)->childAttribute;
		$extraSubJoin->childAttribute->name = 'username';
		$extraSubJoin->childAttribute->alias = 'User_friends.username';
		$extraSubJoin->parentJoin = $friendLink;
		$friendSubJoins->push($extraSubJoin);

		$friendResource->links->removeByPropertyValue('name', 'friends');
		$friendResource->links->removeByPropertyValue('name', 'address');
		$friendResource->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`
AND `User_friendLink`.`username2` = `User_friends`.`username`)
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySet', $querySet);
		$this->assertEquals(2, $querySet->length());

		$userQuerySetItem = $querySet->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $userQuerySetItem);
		$this->assertAttributeEquals('User', 'resourceName', $userQuerySetItem);

		$userLinks = $userQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $userLinks);
		$this->assertEquals(1, $userLinks->length());

		$userFriendLink = $userLinks->getByIndex(0);
		$this->assertSame($resource->links->getByName('friends'), $userFriendLink);

		$userQuery = $userQuerySetItem->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $userQuery);
		$this->assertEquals($expectedQueries[0], (string)$userQuery);

		$friendQuerySetItem = $querySet->getByIndex(1);
		$this->assertInstanceOf('DemoGraph\Module\Graph\QuerySet\QuerySetItem', $friendQuerySetItem);
		$this->assertAttributeEquals('User_friends', 'resourceName', $friendQuerySetItem);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Select', $friendQuerySetItem->getQuery());

		$friendLinks = $friendQuerySetItem->getLinks();
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $friendLinks);
		$this->assertEquals(0, $friendLinks->length());

		$friendQuery = $friendQuerySetItem->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$friendQuery);
	}
}
