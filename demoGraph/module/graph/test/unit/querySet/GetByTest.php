<?php
namespace DemoGraph\Module\Graph\Test\QueryBuilder;

require_once dirname(dirname(__DIR__)) . '/UnitTest.php';

use DemoGraph\Module\Graph\QueryComponent;
use DemoGraph\Module\Graph\QueryFactory;
use DemoGraph\Module\Graph\QuerySet\GetBy;
use DemoGraph\Module\Graph\ResourceDefinition;
use DemoGraph\Module\Graph\Test\Mock\Connection;
use DemoGraph\Test\UnitTest;

class GetByTest extends UnitTest
{
	public function testWithNoLinks()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$queryFactory = new QueryFactory($this->getDatabaseWrapper($dbConnection));

		$resource = $resourceDefinitionBuilder->buildFromName('User');

		$attributesToFetch = new ResourceDefinition\AttributeList();
		$attributesToFetch
			->push($resource->attributes->getByName('id'))
			->push($resource->attributes->getByName('forename'))
			->push($resource->attributes->getByName('surname'));

		$querySet = new GetBy($queryFactory);
		$querySet
			->setResourceDefinition($resource)
			->setAttributes($attributesToFetch)
			->setAttributeValues(array(
				'forename' => 'david'
			));

		$expectedQuery = <<<EOT
SELECT `User`.`id`,`User`.`forename`,`User`.`surname`
FROM `User`
WHERE `User`.`forename` = "david"
EOT;
		$expectedQueryResponse = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham'
			)
		);
		$dbConnection->expectQuery($expectedQuery);
		$dbConnection->pushQueryResponse($expectedQueryResponse);

		$result = $querySet->execute();

		$dbConnection->assertNotExpectingQueries();
		$this->assertEquals($expectedQueryResponse, $result);
	}

	public function testWithOneToOneLink()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$queryFactory = new QueryFactory($this->getDatabaseWrapper($dbConnection));

		$userResource = $resourceDefinitionBuilder->buildFromName('User');
		$addressResource = $resourceDefinitionBuilder->buildFromName('UserAddress', 'address');

		$attributesToFetch = new ResourceDefinition\AttributeList();
		$attributesToFetch
			->push($userResource->attributes->getByName('id'))
			->push($userResource->attributes->getByName('forename'))
			->push($userResource->attributes->getByName('surname'))
			->push($addressResource->attributes->getByName('postcode'));

		$querySet = new GetBy($queryFactory);
		$querySet
			->setResourceDefinition($userResource)
			->setAttributes($attributesToFetch)
			->setAttributeValues(array(
				'forename' => 'david'
			));

		$expectedQuery = <<<EOT
SELECT `User`.`id`,`User`.`forename`,`User`.`surname`,`address`.`postcode`
FROM `User`
INNER JOIN `UserAddress` AS `address` ON (`User`.`id` = `address`.`userId`)
WHERE `User`.`forename` = "david"
EOT;
		$expectedQueryResponse = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham',
				'address.postcode' => 'AB34 5FG'
			)
		);
		$dbConnection->expectQuery($expectedQuery);
		$dbConnection->pushQueryResponse($expectedQueryResponse);

		$expectedAttributes = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham',
				'address' => array(
					'postcode' => 'AB34 5FG'
				)
			)
		);

		$result = $querySet->execute();

		$dbConnection->assertNotExpectingQueries();
		$this->assertEquals($expectedAttributes, $result);
	}

	public function testWithOneToManyLink()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$queryFactory = new QueryFactory($this->getDatabaseWrapper($dbConnection));

		$userResource = $resourceDefinitionBuilder->buildFromName('User');
		$postResource = $userResource->links->getByName('posts')->getChildResource();

		$attributesToFetch = new ResourceDefinition\AttributeList();
		$attributesToFetch
			->push($userResource->attributes->getByName('id'))
			->push($userResource->attributes->getByName('forename'))
			->push($userResource->attributes->getByName('surname'))
			->push($postResource->attributes->getByName('id'))
			->push($postResource->attributes->getByName('content'));

		$querySet = new GetBy($queryFactory);
		$querySet
			->setResourceDefinition($userResource)
			->setAttributes($attributesToFetch)
			->setAttributeValues(array(
				'forename' => 'david'
			));

		$expectedQueries = array();
		$queryResponses = array();

		$expectedQueries[] = <<<EOT
SELECT `User`.`id`,`User`.`forename`,`User`.`surname`
FROM `User`
WHERE `User`.`forename` = "david"
EOT;
		$queryResponses[] = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham'
			)
		);

		$expectedQueries[] = <<<EOT
SELECT `posts`.`id`,`posts`.`content`,`posts`.`authorId`
FROM `Post` AS `posts`
WHERE `posts`.`authorId` IN (1)
EOT;
		$queryResponses[] = array(
			array(
				'authorId' => 1,
				'id' => 1,
				'content' => 'First post'
			),
			array(
				'authorId' => 1,
				'id' => 2,
				'content' => 'Second post'
			)
		);

		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse($queryResponses[0])
			->pushQueryResponse($queryResponses[1]);

		$expectedAttributes = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham',
				'posts' => array(
					array(
						'id' => 1,
						'content' => 'First post'
					),
					array(
						'id' => 2,
						'content' => 'Second post'
					)
				)
			)
		);

		$result = $querySet->execute();

		$dbConnection->assertNotExpectingQueries();
		$this->assertEquals($expectedAttributes, $result);
	}

	public function testWithManyToManyLink()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$dbConnection = new Connection();
		$queryFactory = new QueryFactory($this->getDatabaseWrapper($dbConnection));

		$userResource = $resourceDefinitionBuilder->buildFromName('User');
		$friendResource = $userResource->links->getByName('friends')->getChildResource();

		$attributesToFetch = new ResourceDefinition\AttributeList();
		$attributesToFetch
			->push($userResource->attributes->getByName('id'))
			->push($userResource->attributes->getByName('forename'))
			->push($userResource->attributes->getByName('surname'))
			->push($friendResource->attributes->getByName('id'))
			->push($friendResource->attributes->getByName('forename'))
			->push($friendResource->attributes->getByName('surname'));

		$querySet = new GetBy($queryFactory);
		$querySet
			->setResourceDefinition($userResource)
			->setAttributes($attributesToFetch)
			->setAttributeValues(array(
				'forename' => 'david'
			));

		$expectedQueries = array();
		$queryResponses = array();

		$expectedQueries[] = <<<EOT
SELECT `User`.`id`,`User`.`forename`,`User`.`surname`
FROM `User`
WHERE `User`.`forename` = "david"
EOT;
		$queryResponses[] = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham'
			)
		);

		$expectedQueries[] = <<<EOT
SELECT `friends`.`id`,`friends`.`forename`,`friends`.`surname`,`userFriends`.`friendId1`
FROM `User` AS `friends`
INNER JOIN `UserFriend` AS `userFriends` ON (`userFriends`.`friendId2` = `friends`.`id`)
WHERE `userFriends`.`friendId1` IN (1)
EOT;
		$queryResponses[] = array(
			array(
				'id' => 2,
				'friendId1' => 1,
				'forename' => 'Felicity',
				'surname' => 'Bingham'
			),
			array(
				'id' => 3,
				'friendId1' => 1,
				'forename' => 'Tamsin',
				'surname' => 'Boatman'
			)
		);

		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse($queryResponses[0])
			->pushQueryResponse($queryResponses[1]);

		$expectedAttributes = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham',
				'friends' => array(
					array(
						'id' => 2,
						'forename' => 'Felicity',
						'surname' => 'Bingham'
					),
					array(
						'id' => 3,
						'forename' => 'Tamsin',
						'surname' => 'Boatman'
					)
				)
			)
		);

		$result = $querySet->execute();

		$dbConnection->assertNotExpectingQueries();
		$this->assertEquals($expectedAttributes, $result);
	}

	public function testWithLinksViaOneToOneLink()
	{
		// resource -> resource -> resource, [resource]
	}

	public function testWithLinksViaOneToManyLink()
	{
		// resource -> [resource] => resource, [resource]
	}
}