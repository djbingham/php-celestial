<?php
namespace DemoGraph\Module\Graph\Test\QueryBuilder;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use DemoGraph\Module\Graph\QuerySet\DataParser;
use DemoGraph\Module\Graph\QuerySet\GetBy\Composer;
use DemoGraph\Module\Graph\QuerySet\FilterParser;
use DemoGraph\Module\Graph\QuerySet\GetBy\Conductor;
use DemoGraph\Module\Graph\ResourceDefinition;
use DemoGraph\Module\Graph\Test\Mock\Connection;
use DemoGraph\Test\UnitTest;

class ConductorTest extends UnitTest
{
	public function testQueryConductedFromResourceWithSingleTable()
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
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				),
				array(
					'User.id' => 2,
					'User.forename' => 'Flic',
					'User.surname' => 'Bingham'
				)
			)
		);
		$dbConnection->expectQuery($expectedQuery);
		$dbConnection->pushQueryResponse($expectedData['User']);

		// todo: Properly mock the query set, rather than relying on Composer to build one
		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQueryConductedFromResourceWithSingleTableUsingFilters()
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
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				)
			)
		);
		$dbConnection->expectQuery($expectedQuery);
		$dbConnection->pushQueryResponse($expectedData['User']);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource)
			->setFilters($filters);

		$querySet = $composer->compose();

		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQueryConductedFromResourceWithOneToOneLinksAndFiltersOnLinkedTables()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'friends');
		$resource->links->removeByPropertyValue('name', 'posts');

		$landlordResource = $resource->links->getByName('address')->getChildResource()
			->links->getByName('landlord')->getChildResource();
		$landlordResource->links->removeByPropertyValue('name', 'friends');
		$landlordResource->links->removeByPropertyValue('name', 'posts');

		$landlord2Resource = $landlordResource->links->getByName('address')->getChildResource()
			->links->getByName('landlord')->getChildResource();
		$landlord2Resource->links->removeByPropertyValue('name', 'friends');
		$landlord2Resource->links->removeByPropertyValue('name', 'posts');
		$landlord2Resource->links->removeByPropertyValue('name', 'address');

		// todo: Mock the filters, rather than using FilterParser
		$filters = array(
			'forename' => 'David',
			'address' => array(
				'landlord' => array(
					'forename' => 'Michael'
				)
			)
		);
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
AND `User_address_landlord`.`forename` = "Michael"
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham',
					'User_address.userId' => 1,
					'User_address.houseName' => 'Bingham House',
					'User_address.postcode' => 'BI34 7AM',
					'User_address.landlordId' => 3,
					'User_address_landlord.id' => 3,
					'User_address_landlord.forename' => 'Michael',
					'User_address_landlord.surname' => 'Hughes',
					'User_address_landlord_address.userId' => 3,
					'User_address_landlord_address.houseName' => 'Hughes House',
					'User_address_landlord_address.postcode' => 'HU56 2PM',
					'User_address_landlord_address.landlordId' => 3,
					'User_address_landlord_address_landlord.id' => 3,
					'User_address_landlord_address_landlord.forename' => 'Robert',
					'User_address_landlord_address_landlord.surname' => 'Hughes',
				)
			)
		);
		$dbConnection->expectQuery($expectedQuery);
		$dbConnection->pushQueryResponse($expectedData['User']);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource)
			->setFilters($filters);

		$querySet = $composer->compose();

		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedFromResourceWithOneToManyLink()
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
		$expectedData = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedData['User'] = array(
			array(
				'User.id' => 1,
				'User.forename' => 'David',
				'User.surname' => 'Bingham'
			),
			array(
				'User.id' => 2,
				'User.forename' => 'Flic',
				'User.surname' => 'Bingham'
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_posts`.`id` AS `User_posts.id`,`User_posts`.`authorId` AS `User_posts.authorId`,`User_posts`.`content` AS `User_posts.content`
FROM `Post` AS `User_posts`
WHERE `User_posts`.`authorId` IN (1,2)
EOT;
		$expectedData['User_posts'] = array(
			array(
				'User_posts.id' => 1,
				'User_posts.authorId' => 1,
				'User_posts.content' => 'First post'
			),
			array(
				'User_posts.id' => 2,
				'User_posts.authorId' => 1,
				'User_posts.content' => 'Second post'
			),
			array(
				'User_posts.id' => 3,
				'User_posts.authorId' => 2,
				'User_posts.content' => 'Third post'
			)
		);

		$dbConnection->expectQuerySequence($expectedQueries)
			->pushQueryResponse($expectedData['User'])
			->pushQueryResponse($expectedData['User_posts']);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedFromResourceWithSeveralOneToManyAndOneToOneLinks()
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
		$expectedData = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedData['User'] = array(
			array(
				'User.id' => 1,
				'User.forename' => 'David',
				'User.surname' => 'Bingham'
			),
			array(
				'User.id' => 2,
				'User.forename' => 'Flic',
				'User.surname' => 'Bingham'
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_posts`.`id` AS `User_posts.id`,`User_posts`.`authorId` AS `User_posts.authorId`,`User_posts`.`content` AS `User_posts.content`,`User_posts_author`.`id` AS `User_posts_author.id`,`User_posts_author`.`forename` AS `User_posts_author.forename`,`User_posts_author`.`surname` AS `User_posts_author.surname`
FROM `Post` AS `User_posts`
INNER JOIN `User` AS `User_posts_author` ON (`User_posts`.`authorId` = `User_posts_author`.`id`)
WHERE `User_posts`.`authorId` IN (1,2)
EOT;
		$expectedData['User_posts'] = array(
			array(
				'User_posts.id' => 1,
				'User_posts.content' => 'First post',
				'User_posts.authorId' => 1,
				'User_posts_author.id' => 1,
				'User_posts_author.forename' => 'David',
				'User_posts_author.surname' => 'Bingham'
			),
			array(
				'User_posts.id' => 2,
				'User_posts.content' => 'Second post',
				'User_posts.authorId' => 1,
				'User_posts_author.id' => 1,
				'User_posts_author.forename' => 'David',
				'User_posts_author.surname' => 'Bingham'
			),
			array(
				'User_posts.id' => 3,
				'User_posts.content' => 'Third post',
				'User_posts.authorId' => 2,
				'User_posts_author.id' => 2,
				'User_posts_author.forename' => 'Flic',
				'User_posts_author.surname' => 'Bingham'
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_posts_author_posts`.`id` AS `User_posts_author_posts.id`,`User_posts_author_posts`.`authorId` AS `User_posts_author_posts.authorId`,`User_posts_author_posts`.`content` AS `User_posts_author_posts.content`
FROM `Post` AS `User_posts_author_posts`
WHERE `User_posts_author_posts`.`authorId` IN (1,2)
EOT;
		$expectedData['User_posts_author_posts'] = array(
			array(
				'User_posts_author_posts.id' => 1,
				'User_posts_author_posts.content' => 'First post',
				'User_posts_author_posts.authorId' => 1
			),
			array(
				'User_posts_author_posts.id' => 2,
				'User_posts_author_posts.content' => 'Second post',
				'User_posts_author_posts.authorId' => 1
			),
			array(
				'User_posts_author_posts.id' => 3,
				'User_posts_author_posts.content' => 'Third post',
				'User_posts_author_posts.authorId' => 2
			)
		);

		$dbConnection->expectQuerySequence($expectedQueries)
			->pushQueryResponse($expectedData['User'])
			->pushQueryResponse($expectedData['User_posts'])
			->pushQueryResponse($expectedData['User_posts_author_posts']);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedFromResourceWithManyToManyLink()
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
		$expectedData = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedData['User'] = array(
			array(
				'User.id' => 1,
				'User.forename' => 'David',
				'User.surname' => 'Bingham'
			),
			array(
				'User.id' => 2,
				'User.forename' => 'Flic',
				'User.surname' => 'Bingham'
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
WHERE `User_friendLink`.`friendId1` IN (1,2)
EOT;
		$expectedData['User_friends'] = array(
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Tamsin',
				'User_friends.surname' => 'Boatman'
			),
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Michael',
				'User_friends.surname' => 'Hughes'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Sophie',
				'User_friends.surname' => 'Sutton'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Sarah',
				'User_friends.surname' => 'Berret'
			)
		);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$dbConnection->expectQuerySequence($expectedQueries)
			->pushQueryResponse($expectedData['User'])
			->pushQueryResponse($expectedData['User_friends']);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedFromResourceWithSeveralManyToManyLinks()
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
		$expectedData = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedData['User'] = array(
			array(
				'User.id' => 1,
				'User.forename' => 'David',
				'User.surname' => 'Bingham'
			),
			array(
				'User.id' => 2,
				'User.forename' => 'Flic',
				'User.surname' => 'Bingham'
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
WHERE `User_friendLink`.`friendId1` IN (1,2)
EOT;
		$expectedData['User_friends'] = array(
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Tamsin',
				'User_friends.surname' => 'Boatman'
			),
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Michael',
				'User_friends.surname' => 'Hughes'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Sophie',
				'User_friends.surname' => 'Sutton'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Sarah',
				'User_friends.surname' => 'Berret'
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_friends_friends`.`id` AS `User_friends_friends.id`,`User_friends_friends`.`forename` AS `User_friends_friends.forename`,`User_friends_friends`.`surname` AS `User_friends_friends.surname`,`User_friends_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friends_friendLink`
INNER JOIN `User` AS `User_friends_friends` ON (`User_friends_friendLink`.`friendId2` = `User_friends_friends`.`id`)
WHERE `User_friends_friendLink`.`friendId1` IN (3,4)
EOT;
		$expectedData['User_friends_friends'] = array(
			array(
				'User_friend_friendLink.friendId1' => 1,
				'User_friend_friends.id' => 3,
				'User_friend_friends.forename' => 'Tamsin',
				'User_friend_friends.surname' => 'Boatman'
			),
			array(
				'User_friend_friendLink.friendId1' => 1,
				'User_friend_friends.id' => 4,
				'User_friend_friends.forename' => 'Michael',
				'User_friend_friends.surname' => 'Hughes'
			),
			array(
				'User_friend_friendLink.friendId1' => 2,
				'User_friend_friends.id' => 3,
				'User_friend_friends.forename' => 'Sophie',
				'User_friend_friends.surname' => 'Sutton'
			),
			array(
				'User_friend_friendLink.friendId1' => 2,
				'User_friend_friends.id' => 4,
				'User_friend_friends.forename' => 'Sarah',
				'User_friend_friends.surname' => 'Berret'
			)
		);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$dbConnection->expectQuerySequence($expectedQueries)
			->pushQueryResponse($expectedData['User'])
			->pushQueryResponse($expectedData['User_friends'])
			->pushQueryResponse($expectedData['User_friends_friends']);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedFromResourceWithFiltersOnManyToManyLinkedTables()
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
			'surname' => 'Bingham',
			'friends' => array(
				'surname' => 'Bingham',
				'friends' => array(
					'forename' => 'Michael'
				)
			)
		);
		$filterParser = new FilterParser();
		$filters = $filterParser->parse($resource, $filters);

		$expectedQueries = array();
		$expectedData = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
WHERE `User`.`surname` = "Bingham"
EOT;
		$expectedData['User'] = array(
			array(
				'User.id' => 1,
				'User.forename' => 'David',
				'User.surname' => 'Bingham'
			),
			array(
				'User.id' => 2,
				'User.forename' => 'Flic',
				'User.surname' => 'Bingham'
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
WHERE `User_friends`.`surname` = "Bingham"
AND `User_friendLink`.`friendId1` IN (1,2)
EOT;
		$expectedData['User_friends'] = array(
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Tamsin',
				'User_friends.surname' => 'Boatman'
			),
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Michael',
				'User_friends.surname' => 'Hughes'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Sophie',
				'User_friends.surname' => 'Sutton'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Sarah',
				'User_friends.surname' => 'Berret'
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_friends_friends`.`id` AS `User_friends_friends.id`,`User_friends_friends`.`forename` AS `User_friends_friends.forename`,`User_friends_friends`.`surname` AS `User_friends_friends.surname`,`User_friends_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friends_friendLink`
INNER JOIN `User` AS `User_friends_friends` ON (`User_friends_friendLink`.`friendId2` = `User_friends_friends`.`id`)
WHERE `User_friends_friends`.`forename` = "Michael"
AND `User_friends_friendLink`.`friendId1` IN (3,4)
EOT;
		$expectedData['User_friends_friends'] = array(
			array(
				'User_friends_friendLink.friendId1' => 1,
				'User_friends_friends.id' => 4,
				'User_friends_friends.forename' => 'Michael',
				'User_friends_friends.surname' => 'Hughes'
			)
		);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource)
			->setFilters($filters);

		$querySet = $composer->compose();

		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$dbConnection->expectQuerySequence($expectedQueries)
			->pushQueryResponse($expectedData['User'])
			->pushQueryResponse($expectedData['User_friends'])
			->pushQueryResponse($expectedData['User_friends_friends']);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedFromResourceWithManyToManyLinkHavingMoreThanOneConstraintOnFirstSubJoin()
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

		$extraSubJoin = new ResourceDefinition\TableJoin();
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

		$friendResource->links->removeByPropertyValue('name', 'friends');
		$friendResource->links->removeByPropertyValue('name', 'address');
		$friendResource->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedData = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedData['User'] = array(
			array(
				'User.id' => 1,
				'User.forename' => 'David',
				'User.surname' => 'Bingham'
			),
			array(
				'User.id' => 2,
				'User.forename' => 'Flic',
				'User.surname' => 'Bingham',
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`)
WHERE `User_friendLink`.`friendId1` IN (1,2)
AND `User_friendLink`.`username1` IN ("David","Flic")
EOT;
		$expectedData['User_friends'] = array(
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Tamsin',
				'User_friends.surname' => 'Boatman'
			),
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Michael',
				'User_friends.surname' => 'Hughes'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Sophie',
				'User_friends.surname' => 'Sutton'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Sarah',
				'User_friends.surname' => 'Berret'
			)
		);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$dbConnection->expectQuerySequence($expectedQueries)
			->pushQueryResponse($expectedData['User'])
			->pushQueryResponse($expectedData['User_friends']);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedFromResourceWithManyToManyLinkHavingMoreThanOneConstraintOnSecondSubJoin()
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

		$extraSubJoin = new ResourceDefinition\TableJoin();
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

		$friendResource->links->removeByPropertyValue('name', 'friends');
		$friendResource->links->removeByPropertyValue('name', 'address');
		$friendResource->links->removeByPropertyValue('name', 'posts');

		$expectedQueries = array();
		$expectedData = array();
		$expectedQueries[] = <<<EOT
SELECT `User`.`id` AS `User.id`,`User`.`forename` AS `User.forename`,`User`.`surname` AS `User.surname`
FROM `User`
EOT;
		$expectedData['User'] = array(
			array(
				'User.id' => 1,
				'User.forename' => 'David',
				'User.surname' => 'Bingham'
			),
			array(
				'User.id' => 2,
				'User.forename' => 'Flic',
				'User.surname' => 'Bingham'
			)
		);
		$expectedQueries[] = <<<EOT
SELECT `User_friends`.`id` AS `User_friends.id`,`User_friends`.`forename` AS `User_friends.forename`,`User_friends`.`surname` AS `User_friends.surname`,`User_friendLink`.`friendId1`
FROM `UserFriend` AS `User_friendLink`
INNER JOIN `User` AS `User_friends` ON (`User_friendLink`.`friendId2` = `User_friends`.`id`
AND `User_friendLink`.`username2` = `User_friends`.`username`)
WHERE `User_friendLink`.`friendId1` IN (1,2)
EOT;
		$expectedData['User_friends'] = array(
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Tamsin',
				'User_friends.surname' => 'Boatman'
			),
			array(
				'User_friendLink.friendId1' => 1,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Michael',
				'User_friends.surname' => 'Hughes'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 3,
				'User_friends.forename' => 'Sophie',
				'User_friends.surname' => 'Sutton'
			),
			array(
				'User_friendLink.friendId1' => 2,
				'User_friends.id' => 4,
				'User_friends.forename' => 'Sarah',
				'User_friends.surname' => 'Berret'
			)
		);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setResource($resource);

		$querySet = $composer->compose();

		$dataParser = new DataParser();

		$conductor = new Conductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$dbConnection->expectQuerySequence($expectedQueries)
			->pushQueryResponse($expectedData['User'])
			->pushQueryResponse($expectedData['User_friends']);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}
}
