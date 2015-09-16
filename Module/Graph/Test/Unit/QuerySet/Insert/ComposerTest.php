<?php
namespace Sloth\Module\Graph\Test\Unit\QuerySet\Insert;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use Sloth\Module\Graph\QuerySet\Face\MultiQueryWrapperInterface;
use Sloth\Module\Graph\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Graph\QuerySet\Face\QueryWrapperInterface;
use Sloth\Module\Graph\QuerySet\Face\SingleQueryWrapperInterface;
use Sloth\Module\Graph\QuerySet\Insert\Composer;
use Sloth\Module\Graph\Definition;
use Sloth\Module\Graph\Test\Mock\Connection;
use DemoGraph\Test\UnitTest;

class ComposerTest extends UnitTest
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

		$composer = new Composer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $queryWrapper */
		$queryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $queryWrapper);
		$this->assertSame($table, $queryWrapper->getTable());
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $queryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $queryWrapper->getChildLinks());
		$this->assertEquals(0, $queryWrapper->getChildLinks()->length());

		$query = $queryWrapper->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
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
(`postcode`)
VALUES
("AB34 5FG")
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Graph\QuerySet\Face\QueryLinkInterface', $firstChildLink);
		$this->assertSame($table->links->getByName('address'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $firstChildLink->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $secondQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $secondQueryWrapper->getChildLinks());

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
(`postcode`)
VALUES
("AB34 5FG")
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Graph\QuerySet\Face\QueryLinkInterface', $firstChildLink);
		$this->assertSame($table->links->getByName('address'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $firstChildLink->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $secondQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $secondQueryWrapper->getChildLinks());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertSame($addressTable, $secondQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());
	}

	public function testExceptionIsThrownIfOneToOneLinkSetToAssociateOnInsert()
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

		$table->links->getByName('address')->onInsert = Definition\Table\Join::ACTION_ASSOCIATE;

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'address' => array(
				'postcode' => 'AB34 5FG'
			)
		);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$this->setExpectedException('Sloth\Exception\InvalidRequestException', 'On insert action should not be "associate" for a join that is not many-to-many: User_address');
		$composer->compose();
	}

	public function testQuerySetRejectedIfDataContainsDisallowedSubTableFields()
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

		$addressTable->links->getByName('landlord')->onInsert = Definition\Table\Join::ACTION_REJECT;

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'address' => array(
				'postcode' => 'AB34 5FG',
				'landlord' => array(
					'forename' => 'Michael'
				)
			)
		);

		$composer = new Composer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$this->setExpectedException(
			'Sloth\Exception\InvalidRequestException',
			'Data to insert includes a disallowed subset: User_address_landlord'
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

		$postTable = $table->links->getByName('posts')->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');
		$postTable->links->removeByPropertyValue('name', 'comments');

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
(`content`)
VALUES
("First post")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Post`
(`content`)
VALUES
("Second post")
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$firstQuerySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $firstQuerySet);
		$this->assertEquals(1, $firstQuerySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $firstQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Graph\QuerySet\QueryLink', $firstChildLink);
		$this->assertSame($table->links->getByName('posts'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var MultiQueryWrapperInterface $secondQuerySet */
		$secondQuerySet = $firstChildLink->getChildQueryWrapper();
		$this->assertSame($secondQuerySet, $firstChildLink->getChildQueryWrapper());
		$this->assertEquals(2, $secondQuerySet->length());

		/** @var MultiQueryWrapperInterface $secondQuerySetFirstSubset */
		$secondQuerySetFirstSubset = $secondQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $secondQuerySetFirstSubset);
		$this->assertEquals(1, $secondQuerySetFirstSubset->length());
		$this->assertNull($secondQuerySetFirstSubset->getChildLinks());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $secondQuerySetFirstSubset->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $secondQueryWrapper->getChildLinks());
		$this->assertSame($postTable, $secondQueryWrapper->getTable());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $secondQuery);
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());

		/** @var MultiQueryWrapperInterface $secondQuerySetSecondSubset */
		$secondQuerySetSecondSubset = $secondQuerySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $secondQuerySetSecondSubset);
		$this->assertEquals(1, $secondQuerySetSecondSubset->length());
		$this->assertNull($secondQuerySetSecondSubset->getChildLinks());

		/** @var SingleQueryWrapperInterface $thirdQueryWrapper */
		$thirdQueryWrapper = $secondQuerySetSecondSubset->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $thirdQueryWrapper);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $thirdQueryWrapper->getChildLinks());
		$this->assertSame($postTable, $thirdQueryWrapper->getTable());

		$thirdQuery = $thirdQueryWrapper->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $thirdQuery);
		$this->assertEquals($expectedQueries[2], (string)$thirdQuery);
		$this->assertEquals(0, $thirdQueryWrapper->getChildLinks()->length());
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

		$postTable = $table->links->getByName('posts')->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');
		$postTable->links->removeByPropertyValue('name', 'comments');

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
(`postcode`)
VALUES
("AB34 5FG")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Post`
(`content`)
VALUES
("First post")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Post`
(`content`)
VALUES
("Second post")
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $userInsertQueryWrapper */
		$userInsertQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $userInsertQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $userInsertQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $userInsertQueryWrapper->getChildLinks());

		$userInsertQuery = $userInsertQueryWrapper->getQuery();
		$this->assertSame($table, $userInsertQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$userInsertQuery);
		$this->assertEquals(2, $userInsertQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $linkToAddressInsert */
		$linkToAddressInsert = $userInsertQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Graph\QuerySet\Face\QueryLinkInterface', $linkToAddressInsert);
		$this->assertSame($table->links->getByName('address'), $linkToAddressInsert->getJoinDefinition());

		/** @var QueryLinkInterface $linkToPostInsert */
		$linkToPostInsert = $userInsertQueryWrapper->getChildLinks()->getByIndex(1);
		$this->assertInstanceof('Sloth\Module\Graph\QuerySet\Face\QueryLinkInterface', $linkToPostInsert);
		$this->assertSame($table->links->getByName('posts'), $linkToPostInsert->getJoinDefinition());

		/** @var SingleQueryWrapperInterface $addressInsertQueryWrapper */
		$addressInsertQueryWrapper = $linkToAddressInsert->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $addressInsertQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $addressInsertQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $addressInsertQueryWrapper->getChildLinks());

		/** @var MultiQueryWrapperInterface $postInsertQuerySet */
		$postInsertQuerySet = $linkToPostInsert->getChildQueryWrapper();
		$this->assertEquals(2, $postInsertQuerySet->length());

		/** @var MultiQueryWrapperInterface $firstPostInsertQuerySubSet */
		$firstPostInsertQuerySubSet = $postInsertQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $firstPostInsertQuerySubSet);
		$this->assertEquals(1, $firstPostInsertQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $firstPostInsertQueryWrapper */
		$firstPostInsertQueryWrapper = $firstPostInsertQuerySubSet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $firstPostInsertQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $firstPostInsertQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $firstPostInsertQueryWrapper->getChildLinks());

		/** @var MultiQueryWrapperInterface $secondPostInsertQuerySubSet */
		$secondPostInsertQuerySubSet = $postInsertQuerySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $secondPostInsertQuerySubSet);
		$this->assertEquals(1, $secondPostInsertQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $secondPostInsertQueryWrapper */
		$secondPostInsertQueryWrapper = $secondPostInsertQuerySubSet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $secondPostInsertQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $secondPostInsertQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $secondPostInsertQueryWrapper->getChildLinks());

		$addressInsertQuery = $addressInsertQueryWrapper->getQuery();
		$this->assertSame($addressTable, $addressInsertQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$addressInsertQuery);
		$this->assertEquals(0, $addressInsertQueryWrapper->getChildLinks()->length());

		$firstPostInsertQuery = $firstPostInsertQueryWrapper->getQuery();
		$this->assertSame($postTable, $firstPostInsertQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[2], (string)$firstPostInsertQuery);
		$this->assertEquals(0, $firstPostInsertQueryWrapper->getChildLinks()->length());

		$secondPostInsertQuery = $secondPostInsertQueryWrapper->getQuery();
		$this->assertSame($postTable, $secondPostInsertQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[3], (string)$secondPostInsertQuery);
		$this->assertEquals(0, $secondPostInsertQueryWrapper->getChildLinks()->length());
	}

	public function testQuerySetComposedFromTableWithManyToManyLinkSetToAssociateOnInsert()
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
(`friendId2`)
VALUES
(2)
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `UserFriend`
(`friendId2`)
VALUES
(3)
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $insertUserQueryWrapper */
		$insertUserQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $insertUserQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $insertUserQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $insertUserQueryWrapper->getChildLinks());

		$insertUserQuery = $insertUserQueryWrapper->getQuery();
		$this->assertSame($table, $insertUserQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$insertUserQuery);
		$this->assertEquals(1, $insertUserQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $linkToInsertFriendsQuerySet */
		$linkToInsertFriendsQuerySet = $insertUserQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Graph\QuerySet\Face\QueryLinkInterface', $linkToInsertFriendsQuerySet);
		$this->assertSame($table->links->getByName('friends'), $linkToInsertFriendsQuerySet->getJoinDefinition());

		/** @var MultiQueryWrapperInterface $insertFriendsQuerySetWrapper */
		$insertFriendsQuerySetWrapper = $linkToInsertFriendsQuerySet->getChildQueryWrapper();
		$this->assertEquals(2, $insertFriendsQuerySetWrapper->length());

		/** @var SingleQueryWrapperInterface $insertFirstFriendQueryWrapper */
		$insertFirstFriendQueryWrapper = $insertFriendsQuerySetWrapper->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $insertFirstFriendQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $insertFirstFriendQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $insertFirstFriendQueryWrapper->getChildLinks());

		/** @var SingleQueryWrapperInterface $insertSecondFriendQueryWrapper */
		$insertSecondFriendQueryWrapper = $insertFriendsQuerySetWrapper->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $insertSecondFriendQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $insertSecondFriendQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $insertSecondFriendQueryWrapper->getChildLinks());

		$secondQuery = $insertFirstFriendQueryWrapper->getQuery();
		$this->assertSame($friendLink->intermediaryTables->getByIndex(0), $insertFirstFriendQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $insertFirstFriendQueryWrapper->getChildLinks()->length());

		$thirdQuery = $insertSecondFriendQueryWrapper->getQuery();
		$this->assertSame($friendLink->intermediaryTables->getByIndex(0), $insertSecondFriendQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[2], (string)$thirdQuery);
		$this->assertEquals(0, $insertSecondFriendQueryWrapper->getChildLinks()->length());
	}

	public function testQuerySetComposedFromTableWithChainedOneToManyLinks()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'address');
		$table->links->removeByPropertyValue('name', 'friends');

		$postTable = $table->links->getByName('posts')->getChildTable();
		$postTable->links->removeByPropertyValue('name', 'author');
		$commentTable = $postTable->links->getByName('comments')->getChildTable();
		$commentTable->links->removeByPropertyValue('name', 'author');
		$commentTable->links->removeByPropertyValue('name', 'post');
		$commentTable->links->removeByPropertyValue('name', 'replies');

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
INSERT INTO `User`
(`forename`,`surname`)
VALUES
("David","Bingham")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Post`
(`content`)
VALUES
("First post")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Comment`
(`content`)
VALUES
("First reply to first post")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Comment`
(`content`)
VALUES
("Second reply to first post")
EOT;
		$expectedQueries[] = <<<EOT
INSERT INTO `Post`
(`content`)
VALUES
("Second post")
EOT;

		$composer = new Composer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $insertUserQueryWrapper */
		$insertUserQueryWrapper = $querySet->getByIndex(0);
		$insertUserChildLinks = $insertUserQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $insertUserQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $insertUserQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $insertUserChildLinks);
		$this->assertSame($table, $insertUserQueryWrapper->getTable());
		$this->assertEquals(1, $insertUserChildLinks->length());

		/** @var QueryLinkInterface $linkToInsertPostsQuerySet */
		$linkToInsertPostsQuerySet = $insertUserQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Graph\QuerySet\Face\QueryLinkInterface', $linkToInsertPostsQuerySet);
		$this->assertSame($table->links->getByName('posts'), $linkToInsertPostsQuerySet->getJoinDefinition());

		/** @var MultiQueryWrapperInterface $insertPostsQuerySetWrapper */
		$insertPostsQuerySetWrapper = $linkToInsertPostsQuerySet->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $insertPostsQuerySetWrapper);
		$this->assertEquals(2, $insertPostsQuerySetWrapper->length());

		/** @var MultiQueryWrapperInterface $firstPostQuerySubset */
		$firstPostQuerySubset = $insertPostsQuerySetWrapper->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $firstPostQuerySubset);
		$this->assertEquals(1, $firstPostQuerySubset->length());

		/** @var SingleQueryWrapperInterface $insertFirstPostQueryWrapper */
		$insertFirstPostQueryWrapper = $firstPostQuerySubset->getByIndex(0);
		$firstPostChildLinks = $insertFirstPostQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $insertFirstPostQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $insertFirstPostQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $firstPostChildLinks);
		$this->assertSame($postTable, $insertFirstPostQueryWrapper->getTable());
		$this->assertEquals(1, $firstPostChildLinks->length());

		/** @var QueryLinkInterface $linkToInsertFirstPostComments */
		$linkToInsertFirstPostComments = $firstPostChildLinks->getByIndex(0);

		/** @var MultiQueryWrapperInterface $insertFirstPostCommentsQuerySet */
		$insertFirstPostCommentsQuerySet = $linkToInsertFirstPostComments->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $insertFirstPostCommentsQuerySet);

		/** @var MultiQueryWrapperInterface $firstCommentOnFirstPostQuerySubSet */
		$firstCommentOnFirstPostQuerySubSet = $insertFirstPostCommentsQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $firstCommentOnFirstPostQuerySubSet);
		$this->assertEquals(1, $firstCommentOnFirstPostQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $insertFirstCommentQueryWrapper */
		$insertFirstCommentQueryWrapper = $firstCommentOnFirstPostQuerySubSet->getByIndex(0);
		$firstCommentChildLinks = $insertFirstCommentQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $insertFirstCommentQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $insertFirstCommentQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $firstCommentChildLinks);
		$this->assertSame($commentTable, $insertFirstCommentQueryWrapper->getTable());
		$this->assertEquals(0, $firstCommentChildLinks->length());

		/** @var MultiQueryWrapperInterface $secondCommentOnFirstPostQuerySubSet */
		$secondCommentOnFirstPostQuerySubSet = $insertFirstPostCommentsQuerySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $secondCommentOnFirstPostQuerySubSet);
		$this->assertEquals(1, $secondCommentOnFirstPostQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $insertSecondCommentQueryWrapper */
		$insertSecondCommentQueryWrapper = $secondCommentOnFirstPostQuerySubSet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $insertSecondCommentQueryWrapper);
		$secondCommentChildLinks = $insertSecondCommentQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $insertSecondCommentQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $insertSecondCommentQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $secondCommentChildLinks);
		$this->assertSame($commentTable, $insertSecondCommentQueryWrapper->getTable());
		$this->assertEquals(0, $secondCommentChildLinks->length());

		/** @var MultiQueryWrapperInterface $secondPostQuerySubSet */
		$secondPostQuerySubSet = $insertPostsQuerySetWrapper->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\MultiQueryWrapper', $secondPostQuerySubSet);
		$this->assertEquals(1, $secondPostQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $insertSecondPostQueryWrapper */
		$insertSecondPostQueryWrapper = $secondPostQuerySubSet->getByIndex(0);
		$secondPostChildLinks = $insertSecondPostQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\SingleQueryWrapper', $insertSecondPostQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $insertSecondPostQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Graph\QuerySet\QueryLinkList', $secondPostChildLinks);
		$this->assertSame($postTable, $insertSecondPostQueryWrapper->getTable());
		$this->assertEquals(1, $secondPostChildLinks->length());

		$insertUserQuery = $insertUserQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[0], (string)$insertUserQuery);

		$insertFirstPostQuery = $insertFirstPostQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$insertFirstPostQuery);

		$insertFirstCommentQuery = $insertFirstCommentQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[2], (string)$insertFirstCommentQuery);

		$insertSecondCommentQuery = $insertSecondCommentQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[3], (string)$insertSecondCommentQuery);

		$insertSecondPostQuery = $insertSecondPostQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[4], (string)$insertSecondPostQuery);
	}
}
