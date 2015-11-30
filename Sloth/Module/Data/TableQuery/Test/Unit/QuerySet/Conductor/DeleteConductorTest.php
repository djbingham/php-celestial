<?php
namespace Sloth\Module\Data\TableQuery\Test\Unit\QuerySet\Conductor;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use Sloth\Module\Data\Table\Definition;
use Sloth\Module\Data\TableQuery\QuerySet\DataParser;
use Sloth\Module\Data\TableQuery\QuerySet\Composer\DeleteComposer;
use Sloth\Module\Data\TableQuery\QuerySet\Conductor\DeleteConductor;
use Sloth\Module\Data\TableQuery\Test\Mock\Connection;
use Sloth\Module\Data\TableQuery\Test\UnitTest;

class DeleteConductorTest extends UnitTest
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
DELETE FROM `User`
WHERE `User`.`id` = 1
EOT;
		$dbConnection->expectQuery($expectedQuery);
		$dbConnection->pushQueryResponse(null);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new DeleteConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertNull($output);

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
DELETE FROM `User`
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `UserAddress`
WHERE `UserAddress`.`userId` = 7
EOT;
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushInsertId(11);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new DeleteConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertNull($output);

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
DELETE FROM `User`
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `UserAddress`
WHERE `UserAddress`.`userId` = 7
EOT;
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushInsertId(11);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new DeleteConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertNull($output);

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

		$data = array(
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
DELETE FROM `User`
WHERE `User`.`id` = 7
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `Post`
WHERE `Post`.`id` = 12
OR `Post`.`id` = 13
EOT;
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new DeleteConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertNull($output);

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

		$data = array(
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
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new DeleteConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertNull($output);

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

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'posts' => array(
				array(
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
					'content' => 'Second post'
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
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new DeleteConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertNull($output);

		$dbConnection->assertNotExpectingQueries();
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
DELETE FROM `User`
WHERE `User`.`id` = 1
EOT;
		$expectedQueries[] = <<<EOT
DELETE FROM `UserFriend`
WHERE `UserFriend`.`friendId1` = 1
EOT;
		$dbConnection->expectQuerySequence($expectedQueries);
		$dbConnection->pushQueryResponse(null);
		$dbConnection->pushQueryResponse(null);

		$composer = new DeleteComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		// todo: Mock the data parser
		$dataParser = new DataParser();

		$conductor = new DeleteConductor();
		$conductor->setDatabase($database)
			->setQuerySet($querySet)
			->setDataParser($dataParser);

		$output = $conductor->conduct();
		$this->assertNull($output);

		$dbConnection->assertNotExpectingQueries();
	}
}
