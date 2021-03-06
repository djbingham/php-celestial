<?php
namespace Celestial\Module\Data\TableQuery\Test\Unit\QuerySet\Conductor;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use Celestial\Module\Data\Table\Definition;
use Celestial\Module\Data\TableQuery\QuerySet\DataParser;
use Celestial\Module\Data\TableQuery\QuerySet\Composer\UpdateComposer;
use Celestial\Module\Data\TableQuery\QuerySet\Conductor\UpdateConductor;
use Celestial\Module\Data\TableQuery\Test\Mock\Connection;
use Celestial\Module\Data\TableQuery\Test\UnitTest;

class UpdateConductorTest extends UnitTest
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

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham'
		);

		$expectedQuery = <<<EOT
UPDATE `User`
SET `User`.`forename` = "David",
	`User`.`surname` = "Bingham"
WHERE `User`.`id` = 1
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => '1',
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				)
			)
		);
		$dbConnection->expectQuery($expectedQuery);
		$dbConnection->pushQueryResponse(null);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new UpdateConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertEquals($expectedData, $output);

		$dbConnection->assertNotExpectingQueries();
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
				'userId' => 7
			)
		);

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'address' => array(
				'postcode' => 'AB34 5FG'
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
UPDATE `User`
SET `User`.`forename` = "David",
	`User`.`surname` = "Bingham"
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `UserAddress`
SET `UserAddress`.`postcode` = "AB34 5FG",
	`UserAddress`.`userId` = 7
WHERE `UserAddress`.`userId` = 7
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.forename' => 'David',
					'User.surname' => 'Bingham',
					'User.id' => 7
				)
			),
			'UserAddress' => array(
				array(
					'User_address.postcode' => 'AB34 5FG',
					'User_address.userId' => 7
				)
			)
		);
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushInsertId(11);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new UpdateConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertEquals($expectedData, $output);

		$dbConnection->assertNotExpectingQueries();
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
				'userId' => 7
			)
		);

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'address' => array(
				'postcode' => 'AB34 5FG'
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
UPDATE `User`
SET `User`.`forename` = "David",
	`User`.`surname` = "Bingham"
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `UserAddress`
SET `UserAddress`.`postcode` = "AB34 5FG",
	`UserAddress`.`userId` = 7
WHERE `UserAddress`.`userId` = 7
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.forename' => 'David',
					'User.surname' => 'Bingham',
					'User.id' => 7
				)
			),
			'UserAddress' => array(
				array(
					'User_address.postcode' => 'AB34 5FG',
					'User_address.userId' => 7
				)
			)
		);
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushInsertId(11);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new UpdateConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertEquals($expectedData, $output);

		$dbConnection->assertNotExpectingQueries();
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
		$linkToPosts->onUpdate = Definition\Table\Join::ACTION_UPDATE;

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

		$data = array(
			'id' => 7,
			'forename' => 'David',
			'surname' => 'Bingham',
			'posts' => array(
				array(
					'authorId' => 7,
					'content' => 'First updated post'
				),
				array(
					'authorId' => 7,
					'content' => 'Second updated post'
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
UPDATE `User`
SET `User`.`id` = 7,
	`User`.`forename` = "David",
	`User`.`surname` = "Bingham"
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Post`
SET `Post`.`authorId` = 7,
	`Post`.`content` = "First updated post"
WHERE `Post`.`id` = 12
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Post`
SET `Post`.`authorId` = 7,
	`Post`.`content` = "Second updated post"
WHERE `Post`.`id` = 13
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.forename' => 'David',
					'User.surname' => 'Bingham',
					'User.id' => 7
				)
			),
			'Post' => array(
				array(
					'User_posts.authorId' => 7,
					'User_posts.content' => 'First updated post',
					'User_posts.id' => 12
				),
				array(
					'User_posts.authorId' => 7,
					'User_posts.content' => 'Second updated post',
					'User_posts.id' => 13
				)
			)
		);
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new UpdateConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertEquals($expectedData, $output);

		$dbConnection->assertNotExpectingQueries();
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
		$linkToPosts->onUpdate = Definition\Table\Join::ACTION_UPDATE;

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

		$data = array(
			'id' => 7,
			'forename' => 'David',
			'surname' => 'Bingham',
			'address' => array(
				'postcode' => 'AB34 5FG'
			),
			'posts' => array(
				array(
					'content' => 'First updated post'
				),
				array(
					'content' => 'Second updated post'
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
UPDATE `User`
SET `User`.`id` = 7,
	`User`.`forename` = "David",
	`User`.`surname` = "Bingham"
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `UserAddress`
SET `UserAddress`.`postcode` = "AB34 5FG",
	`UserAddress`.`userId` = 7
WHERE `UserAddress`.`userId` = 7
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Post`
SET `Post`.`content` = "First updated post",
	`Post`.`authorId` = 7
WHERE `Post`.`id` = 12
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Post`
SET `Post`.`content` = "Second updated post",
	`Post`.`authorId` = 7
WHERE `Post`.`id` = 13
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 7,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				)
			),
			'UserAddress' => array(
				array(
					'User_address.postcode' => 'AB34 5FG',
					'User_address.userId' => 7
				)
			),
			'Post' => array(
				array(
					'User_posts.content' => 'First updated post',
					'User_posts.authorId' => 7,
					'User_posts.id' => 12
				),
				array(
					'User_posts.content' => 'Second updated post',
					'User_posts.authorId' => 7,
					'User_posts.id' => 13
				)
			)
		);
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new UpdateConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertEquals($expectedData, $output);

		$dbConnection->assertNotExpectingQueries();
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
		$linkToPosts->onUpdate = Definition\Table\Join::ACTION_UPDATE;

		$postTable = $linkToPosts->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');

		$linkToComments = $postTable->links->getByName('comments');
		$linkToComments->onUpdate = Definition\Table\Join::ACTION_UPDATE;

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

		$data = array(
			'id' => 1,
			'forename' => 'David',
			'surname' => 'Bingham',
			'posts' => array(
				array(
					'id' => 11,
					'content' => 'First post',
					'comments' => array(
						array(
							'content' => 'First reply to first post'
						),
						array(
							'content' => 'Second reply to first post'
						)
					)
				),
				array(
					'id' => 12,
					'content' => 'Second post'
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
UPDATE `User`
SET `User`.`id` = 1,
	`User`.`forename` = "David",
	`User`.`surname` = "Bingham"
WHERE `User`.`id` = 1
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Post`
SET `Post`.`id` = 11,
	`Post`.`content` = "First post",
	`Post`.`authorId` = 1
WHERE `Post`.`id` = 11
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Comment`
SET `Comment`.`content` = "First reply to first post",
	`Comment`.`postId` = 11
WHERE `Comment`.`id` = 21
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Comment`
SET `Comment`.`content` = "Second reply to first post",
	`Comment`.`postId` = 11
WHERE `Comment`.`id` = 22
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Post`
SET `Post`.`id` = 12,
	`Post`.`content` = "Second post",
	`Post`.`authorId` = 1
WHERE `Post`.`id` = 12
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				)
			),
			'Post' => array(
				array(
					'User_posts.content' => 'First post',
					'User_posts.authorId' => 1,
					'User_posts.id' => 11
				),
				array(
					'User_posts.content' => 'Second post',
					'User_posts.authorId' => 1,
					'User_posts.id' => 12
				)
			),
			'Comment' => array(
				array(
					'User_posts_comments.content' => 'First reply to first post',
					'User_posts_comments.id' => 21,
					'User_posts_comments.postId' => 11
				),
				array(
					'User_posts_comments.content' => 'Second reply to first post',
					'User_posts_comments.id' => 22,
					'User_posts_comments.postId' => 11
				)
			)
		);
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new UpdateConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertEquals($expectedData, $output);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetComposedFromTableWithManyToManyLinkSetToAssociateOnUpdate()
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

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'friends' => array(
				array(
					'id' => 2,
					'forename' => 'Flic',
					'surname' => 'Bingham'
				),
				array(
					'id' => 3,
					'forename' => 'Michael',
					'surname' => 'Hughes'
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
UPDATE `User`
SET `User`.`forename` = "David",
	`User`.`surname` = "Bingham"
WHERE `User`.`id` = 1
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `UserFriend`
WHERE `UserFriend`.`friendId1` = 1
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `UserFriend`
(`friendId1`,`friendId2`)
VALUES
(1,2),
(1,3)
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				)
			),
			'UserFriend' => array(
				array(
					'User_friendLink.friendId1' => 1,
					'User_friendLink.friendId2' => 2
				),
				array(
					'User_friendLink.friendId1' => 1,
					'User_friendLink.friendId2' => 3
				)
			)
		);
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushInsertId(1);
		$dbConnection->pushInsertId(null);
		$dbConnection->pushInsertId(null);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new UpdateConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertEquals($expectedData, $output);

		$dbConnection->assertNotExpectingQueries();
	}
}
