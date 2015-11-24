<?php
namespace Sloth\Module\DataTableQuery\Test\Unit\QuerySet\Conductor;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use Sloth\Module\DataTable\Definition;
use Sloth\Module\DataTableQuery\QuerySet\DataParser;
use Sloth\Module\DataTableQuery\QuerySet\Composer\InsertComposer;
use Sloth\Module\DataTableQuery\QuerySet\Conductor\InsertConductor;
use Sloth\Module\DataTableQuery\Test\Mock\Connection;
use Sloth\Module\DataTableQuery\Test\UnitTest;

class InsertConductorTest extends UnitTest
{
	public function testQuerySetConductedForSingleTable()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		while ($table->links->length() > 0) {
			$table->links->removeByIndex(0);
		}

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham'
		);

		$expectedQuery = <<<EOT
INSERT INTO `User`
(`forename`,`surname`)
VALUES
("David","Bingham")
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 11,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				)
			)
		);
		$dbConnection->expectQuery($expectedQuery);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushInsertId(11);

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new InsertConductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$output = $conductor->conduct();
		$this->assertEquals($expectedData, $output);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedForTablesWithOneToOneLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'posts');

		$addressTable = $table->links->getByName('address')->getChildTable();
		$addressTable->links->removeByPropertyValue('name', 'landlord');

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'address' => array(
				'postcode' => 'AB34 5FG'
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
INSERT INTO `User`
(`forename`,`surname`)
VALUES
("David","Bingham")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `UserAddress`
(`postcode`,`userId`)
VALUES
("AB34 5FG",11)
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 11,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				)
			),
			'UserAddress' => array(
				array(
					'User_address.userId' => 11,
					'User_address.postcode' => 'AB34 5FG'
				)
			)
		);
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushInsertId(11);

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new InsertConductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedForTableWithOneToManyLinkData()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'address');

		$postTable = $table->links->getByName('posts')->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'posts' => array(
				array(
					'content' => 'First post'
				),
				array(
					'content' => 'Second post'
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
INSERT INTO `User`
(`forename`,`surname`)
VALUES
("David","Bingham")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Post`
(`content`,`authorId`)
VALUES
("First post",11)
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Post`
(`content`,`authorId`)
VALUES
("Second post",11)
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 11,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				)
			),
			'Post' => array(
				array(
					'User_posts.id' => 17,
					'User_posts.authorId' => 11,
					'User_posts.content' => 'First post'
				),
				array(
					'User_posts.id' => 18,
					'User_posts.authorId' => 11,
					'User_posts.content' => 'Second post'
				)
			)
		);
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushInsertId(11);
		$dbConnection->pushInsertId(17);
		$dbConnection->pushInsertId(18);

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new InsertConductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedForTableWithSeveralLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');

		$postTable = $table->links->getByName('posts')->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'address' => array(
				'postcode' => 'AB34 5FG'
			),
			'posts' => array(
				array(
					'content' => 'First post'
				),
				array(
					'content' => 'Second post'
				)
			)
		);

		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
INSERT INTO `User`
(`forename`,`surname`)
VALUES
("David","Bingham")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `UserAddress`
(`postcode`,`userId`)
VALUES
("AB34 5FG",11)
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Post`
(`content`,`authorId`)
VALUES
("First post",11)
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Post`
(`content`,`authorId`)
VALUES
("Second post",11)
EOT;
		$expectedData = array(
			'User' => array(
				array(
					'User.id' => 11,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				)
			),
			'UserAddress' => array(
				array(
					'User_address.userId' => 11,
					'User_address.postcode' => 'AB34 5FG'
				)
			),
			'Post' => array(
				array(
					'User_posts.id' => 17,
					'User_posts.authorId' => 11,
					'User_posts.content' => 'First post'
				),
				array(
					'User_posts.id' => 18,
					'User_posts.authorId' => 11,
					'User_posts.content' => 'Second post'
				)
			)
		);
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushInsertId(11);
		$dbConnection->pushInsertId(null);
		$dbConnection->pushInsertId(17);
		$dbConnection->pushInsertId(18);

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new InsertConductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}

	public function testQuerySetConductedForTableWithManyToManyLinkSetToAssociateOnInsert()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'address');
		$table->links->removeByPropertyValue('name', 'posts');

		$friendTable = $table->links->getByName('friends')->getChildTable();
		$friendTable->links->removeByPropertyValue('name', 'posts');
		$friendTable->links->removeByPropertyValue('name', 'address');
		$friendTable->links->removeByPropertyValue('name', 'friends');

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
INSERT INTO `User`
(`forename`,`surname`)
VALUES
("David","Bingham")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `UserFriend`
(`friendId2`,`friendId1`)
VALUES
(2,1)
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `UserFriend`
(`friendId2`,`friendId1`)
VALUES
(3,1)
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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new InsertConductor();
		$conductor->setDatabase($database)
			->setDataParser($dataParser)
			->setQuerySet($querySet);

		$data = $conductor->conduct();
		$this->assertEquals($expectedData, $data);

		$dbConnection->assertNotExpectingQueries();
	}
}
