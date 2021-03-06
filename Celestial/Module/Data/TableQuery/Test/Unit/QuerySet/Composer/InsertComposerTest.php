<?php
namespace Celestial\Module\Data\TableQuery\Test\Unit\QuerySet\Composer;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use Celestial\Module\Data\Table\Definition;
use Celestial\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Composer\InsertComposer;
use Celestial\Module\Data\TableQuery\Test\Mock\Connection;
use Celestial\Module\Data\TableQuery\Test\UnitTest;

class InsertComposerTest extends UnitTest
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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $queryWrapper */
		$queryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $queryWrapper);
		$this->assertSame($table, $queryWrapper->getTable());
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $queryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $queryWrapper->getChildLinks());
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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $firstChildLink);
		$this->assertSame($table->links->getByName('address'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $firstChildLink->getChildQueryWrapper();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $secondQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertSame($addressTable, $secondQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());
	}

	public function testQueryNotBuiltForOneToOneChildTableWithNoData()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		$table->links->removeByPropertyValue('name', 'friends');
		$table->links->removeByPropertyValue('name', 'posts');

		$addressTable = $table->links->getByName('address')->getChildTable();
		$addressTable->links->removeByPropertyValue('name', 'landlord');

		// Don't include address table in data.
		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham'
		);

		// Expect no insert query for address table, even though it is part of the resource, since it has no data
		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
INSERT INTO `User`
(`forename`,`surname`)
VALUES
("David","Bingham")
EOT;

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $firstChildLink);
		$this->assertSame($table->links->getByName('address'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $firstChildLink->getChildQueryWrapper();
		$this->assertNull($secondQueryWrapper);
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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $firstChildLink);
		$this->assertSame($table->links->getByName('address'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $firstChildLink->getChildQueryWrapper();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $secondQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());

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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$this->setExpectedException('Celestial\Exception\InvalidRequestException', 'On insert action should not be "associate" for a join that is not many-to-many: User_address');
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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$this->setExpectedException(
			'Celestial\Exception\InvalidRequestException',
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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$firstQuerySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $firstQuerySet);
		$this->assertEquals(1, $firstQuerySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $firstQuerySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLink', $firstChildLink);
		$this->assertSame($table->links->getByName('posts'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var MultiQueryWrapperInterface $secondQuerySet */
		$secondQuerySet = $firstChildLink->getChildQueryWrapper();
		$this->assertSame($secondQuerySet, $firstChildLink->getChildQueryWrapper());
		$this->assertEquals(2, $secondQuerySet->length());

		/** @var MultiQueryWrapperInterface $secondQuerySetFirstSubset */
		$secondQuerySetFirstSubset = $secondQuerySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondQuerySetFirstSubset);
		$this->assertEquals(1, $secondQuerySetFirstSubset->length());
		$this->assertNull($secondQuerySetFirstSubset->getChildLinks());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $secondQuerySetFirstSubset->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());
		$this->assertSame($postTable, $secondQueryWrapper->getTable());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $secondQuery);
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());

		/** @var MultiQueryWrapperInterface $secondQuerySetSecondSubset */
		$secondQuerySetSecondSubset = $secondQuerySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondQuerySetSecondSubset);
		$this->assertEquals(1, $secondQuerySetSecondSubset->length());
		$this->assertNull($secondQuerySetSecondSubset->getChildLinks());

		/** @var SingleQueryWrapperInterface $thirdQueryWrapper */
		$thirdQueryWrapper = $secondQuerySetSecondSubset->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $thirdQueryWrapper);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $thirdQueryWrapper->getChildLinks());
		$this->assertSame($postTable, $thirdQueryWrapper->getTable());

		$thirdQuery = $thirdQueryWrapper->getQuery();
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $thirdQuery);
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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $userInsertQueryWrapper */
		$userInsertQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $userInsertQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $userInsertQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $userInsertQueryWrapper->getChildLinks());

		$userInsertQuery = $userInsertQueryWrapper->getQuery();
		$this->assertSame($table, $userInsertQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$userInsertQuery);
		$this->assertEquals(2, $userInsertQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $linkToAddressInsert */
		$linkToAddressInsert = $userInsertQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $linkToAddressInsert);
		$this->assertSame($table->links->getByName('address'), $linkToAddressInsert->getJoinDefinition());

		/** @var QueryLinkInterface $linkToPostInsert */
		$linkToPostInsert = $userInsertQueryWrapper->getChildLinks()->getByIndex(1);
		$this->assertInstanceof('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $linkToPostInsert);
		$this->assertSame($table->links->getByName('posts'), $linkToPostInsert->getJoinDefinition());

		/** @var SingleQueryWrapperInterface $addressInsertQueryWrapper */
		$addressInsertQueryWrapper = $linkToAddressInsert->getChildQueryWrapper();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $addressInsertQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $addressInsertQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $addressInsertQueryWrapper->getChildLinks());

		/** @var MultiQueryWrapperInterface $postInsertQuerySet */
		$postInsertQuerySet = $linkToPostInsert->getChildQueryWrapper();
		$this->assertEquals(2, $postInsertQuerySet->length());

		/** @var MultiQueryWrapperInterface $firstPostInsertQuerySubSet */
		$firstPostInsertQuerySubSet = $postInsertQuerySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $firstPostInsertQuerySubSet);
		$this->assertEquals(1, $firstPostInsertQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $firstPostInsertQueryWrapper */
		$firstPostInsertQueryWrapper = $firstPostInsertQuerySubSet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstPostInsertQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $firstPostInsertQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstPostInsertQueryWrapper->getChildLinks());

		/** @var MultiQueryWrapperInterface $secondPostInsertQuerySubSet */
		$secondPostInsertQuerySubSet = $postInsertQuerySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondPostInsertQuerySubSet);
		$this->assertEquals(1, $secondPostInsertQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $secondPostInsertQueryWrapper */
		$secondPostInsertQueryWrapper = $secondPostInsertQuerySubSet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondPostInsertQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $secondPostInsertQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondPostInsertQueryWrapper->getChildLinks());

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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $insertUserQueryWrapper */
		$insertUserQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertUserQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $insertUserQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $insertUserQueryWrapper->getChildLinks());

		$insertUserQuery = $insertUserQueryWrapper->getQuery();
		$this->assertSame($table, $insertUserQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$insertUserQuery);
		$this->assertEquals(1, $insertUserQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $linkToInsertFriendsQuerySet */
		$linkToInsertFriendsQuerySet = $insertUserQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $linkToInsertFriendsQuerySet);
		$this->assertSame($table->links->getByName('friends'), $linkToInsertFriendsQuerySet->getJoinDefinition());

		/** @var MultiQueryWrapperInterface $insertFriendsQuerySetWrapper */
		$insertFriendsQuerySetWrapper = $linkToInsertFriendsQuerySet->getChildQueryWrapper();
		$this->assertEquals(2, $insertFriendsQuerySetWrapper->length());

		/** @var SingleQueryWrapperInterface $insertFirstFriendQueryWrapper */
		$insertFirstFriendQueryWrapper = $insertFriendsQuerySetWrapper->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertFirstFriendQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $insertFirstFriendQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $insertFirstFriendQueryWrapper->getChildLinks());

		/** @var SingleQueryWrapperInterface $insertSecondFriendQueryWrapper */
		$insertSecondFriendQueryWrapper = $insertFriendsQuerySetWrapper->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertSecondFriendQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $insertSecondFriendQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $insertSecondFriendQueryWrapper->getChildLinks());

		$secondQuery = $insertFirstFriendQueryWrapper->getQuery();
		$this->assertSame($friendLink->intermediaryTables->getByIndex(0), $insertFirstFriendQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $insertFirstFriendQueryWrapper->getChildLinks()->length());

		$thirdQuery = $insertSecondFriendQueryWrapper->getQuery();
		$this->assertSame($friendLink->intermediaryTables->getByIndex(0), $insertSecondFriendQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[2], (string)$thirdQuery);
		$this->assertEquals(0, $insertSecondFriendQueryWrapper->getChildLinks()->length());
	}

	public function testQueryNotBuildForManyToManyLinkTableWithNoData()
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

		// Don't include any data for friends
		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham'
		);

		// Expect no insert queries for friend table, even though it is part of resource, since it has no data
		$expectedQueries = array();
		$expectedQueries[] = <<<EOT
INSERT INTO `User`
(`forename`,`surname`)
VALUES
("David","Bingham")
EOT;

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $insertUserQueryWrapper */
		$insertUserQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertUserQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $insertUserQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $insertUserQueryWrapper->getChildLinks());

		$insertUserQuery = $insertUserQueryWrapper->getQuery();
		$this->assertSame($table, $insertUserQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$insertUserQuery);
		$this->assertEquals(1, $insertUserQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $linkToInsertFriendsQuerySet */
		$linkToInsertFriendsQuerySet = $insertUserQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $linkToInsertFriendsQuerySet);
		$this->assertSame($table->links->getByName('friends'), $linkToInsertFriendsQuerySet->getJoinDefinition());

		/** @var MultiQueryWrapperInterface $insertFriendsQuerySetWrapper */
		$insertFriendsQuerySetWrapper = $linkToInsertFriendsQuerySet->getChildQueryWrapper();
		$this->assertNull($insertFriendsQuerySetWrapper);
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

		$composer = new InsertComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $insertUserQueryWrapper */
		$insertUserQueryWrapper = $querySet->getByIndex(0);
		$insertUserChildLinks = $insertUserQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertUserQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $insertUserQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $insertUserChildLinks);
		$this->assertSame($table, $insertUserQueryWrapper->getTable());
		$this->assertEquals(1, $insertUserChildLinks->length());

		/** @var QueryLinkInterface $linkToInsertPostsQuerySet */
		$linkToInsertPostsQuerySet = $insertUserQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $linkToInsertPostsQuerySet);
		$this->assertSame($table->links->getByName('posts'), $linkToInsertPostsQuerySet->getJoinDefinition());

		/** @var MultiQueryWrapperInterface $insertPostsQuerySetWrapper */
		$insertPostsQuerySetWrapper = $linkToInsertPostsQuerySet->getChildQueryWrapper();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $insertPostsQuerySetWrapper);
		$this->assertEquals(2, $insertPostsQuerySetWrapper->length());

		/** @var MultiQueryWrapperInterface $firstPostQuerySubset */
		$firstPostQuerySubset = $insertPostsQuerySetWrapper->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $firstPostQuerySubset);
		$this->assertEquals(1, $firstPostQuerySubset->length());

		/** @var SingleQueryWrapperInterface $insertFirstPostQueryWrapper */
		$insertFirstPostQueryWrapper = $firstPostQuerySubset->getByIndex(0);
		$firstPostChildLinks = $insertFirstPostQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertFirstPostQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $insertFirstPostQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstPostChildLinks);
		$this->assertSame($postTable, $insertFirstPostQueryWrapper->getTable());
		$this->assertEquals(1, $firstPostChildLinks->length());

		/** @var QueryLinkInterface $linkToInsertFirstPostComments */
		$linkToInsertFirstPostComments = $firstPostChildLinks->getByIndex(0);

		/** @var MultiQueryWrapperInterface $insertFirstPostCommentsQuerySet */
		$insertFirstPostCommentsQuerySet = $linkToInsertFirstPostComments->getChildQueryWrapper();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $insertFirstPostCommentsQuerySet);

		/** @var MultiQueryWrapperInterface $firstCommentOnFirstPostQuerySubSet */
		$firstCommentOnFirstPostQuerySubSet = $insertFirstPostCommentsQuerySet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $firstCommentOnFirstPostQuerySubSet);
		$this->assertEquals(1, $firstCommentOnFirstPostQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $insertFirstCommentQueryWrapper */
		$insertFirstCommentQueryWrapper = $firstCommentOnFirstPostQuerySubSet->getByIndex(0);
		$firstCommentChildLinks = $insertFirstCommentQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertFirstCommentQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $insertFirstCommentQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstCommentChildLinks);
		$this->assertSame($commentTable, $insertFirstCommentQueryWrapper->getTable());
		$this->assertEquals(0, $firstCommentChildLinks->length());

		/** @var MultiQueryWrapperInterface $secondCommentOnFirstPostQuerySubSet */
		$secondCommentOnFirstPostQuerySubSet = $insertFirstPostCommentsQuerySet->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondCommentOnFirstPostQuerySubSet);
		$this->assertEquals(1, $secondCommentOnFirstPostQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $insertSecondCommentQueryWrapper */
		$insertSecondCommentQueryWrapper = $secondCommentOnFirstPostQuerySubSet->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertSecondCommentQueryWrapper);
		$secondCommentChildLinks = $insertSecondCommentQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertSecondCommentQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $insertSecondCommentQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondCommentChildLinks);
		$this->assertSame($commentTable, $insertSecondCommentQueryWrapper->getTable());
		$this->assertEquals(0, $secondCommentChildLinks->length());

		/** @var MultiQueryWrapperInterface $secondPostQuerySubSet */
		$secondPostQuerySubSet = $insertPostsQuerySetWrapper->getByIndex(1);
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondPostQuerySubSet);
		$this->assertEquals(1, $secondPostQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $insertSecondPostQueryWrapper */
		$insertSecondPostQueryWrapper = $secondPostQuerySubSet->getByIndex(0);
		$secondPostChildLinks = $insertSecondPostQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $insertSecondPostQueryWrapper);
		$this->assertInstanceOf('PhpMySql\QueryBuilder\Query\Insert', $insertSecondPostQueryWrapper->getQuery());
		$this->assertInstanceOf('Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondPostChildLinks);
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
