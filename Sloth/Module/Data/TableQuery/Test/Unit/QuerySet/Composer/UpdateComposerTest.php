<?php
namespace Sloth\Module\Data\TableQuery\Test\Unit\QuerySet\Composer;

require_once dirname(dirname(dirname(__DIR__))) . '/UnitTest.php';

use Sloth\Module\Data\Table\Definition;
use Sloth\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Composer\UpdateComposer;
use Sloth\Module\Data\TableQuery\Test\Mock\Connection;
use Sloth\Module\Data\TableQuery\Test\UnitTest;

class UpdateComposerTest extends UnitTest
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

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $queryWrapper */
		$queryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $queryWrapper);
		$this->assertSame($table, $queryWrapper->getTable());
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $queryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $queryWrapper->getChildLinks());
		$this->assertEquals(0, $queryWrapper->getChildLinks()->length());

		$query = $queryWrapper->getQuery();
		$this->assertEquals($expectedQuery, (string)$query);
	}

	public function testQuerySetRejectedIfNoFiltersGiven()
	{
		$tableDefinitionBuilder = $this->getTableDefinitionBuilder();
		$dbConnection = new Connection();
		$database = $this->getDatabaseWrapper($dbConnection);

		$table = $tableDefinitionBuilder->buildFromName('User');
		while ($table->links->length() > 0) {
			$table->links->removeByIndex(0);
		}

		$filters = array();

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
		);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$this->setExpectedException(
			'Sloth\Exception\InvalidRequestException',
			'No filters given for table: User'
		);
		$composer->compose();
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
				'userId' => 12
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
SET `UserAddress`.`postcode` = "AB34 5FG"
WHERE `UserAddress`.`userId` = 12
EOT;

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $firstChildLink);
		$this->assertSame($table->links->getByName('address'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $firstChildLink->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $secondQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());

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

		$filters = array(
			'id' => 7,
			'address' => array(
				'userId' => 12
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
SET `UserAddress`.`postcode` = "AB34 5FG"
WHERE `UserAddress`.`userId` = 12
EOT;

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $firstChildLink);
		$this->assertSame($table->links->getByName('address'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $firstChildLink->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $secondQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertSame($addressTable, $secondQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());
	}

	public function testExceptionIsThrownIfOneToOneLinkSetToAssociateOnUpdate()
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

		$table->links->getByName('address')->onUpdate = Definition\Table\Join::ACTION_ASSOCIATE;

		$filters = array(
			'id' => 1,
			'address' => array(
				'userId' => 1
			)
		);

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'address' => array(
				'postcode' => 'AB34 5FG'
			)
		);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$this->setExpectedException('Sloth\Exception\InvalidRequestException', 'On update action should not be "associate" for a join that is not many-to-many: User_address');
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

		$addressTable->links->getByName('landlord')->onUpdate = Definition\Table\Join::ACTION_REJECT;

		$filters = array(
			'id' => 1,
			'address' => array(
				'userId' => 1,
				'landlord' => array(
					'id' => 1
				)
			)
		);

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

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$this->setExpectedException(
			'Sloth\Exception\InvalidRequestException',
			'Data to update includes a disallowed subset: User_address_landlord'
		);
		$composer->compose();
	}

	public function testQuerySetRejectedIfNoFiltersGivenForSubTable()
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

		$filters = array(
			'id' => 1,
			'address' => array(

			)
		);

		$data = array(
			'forename' => 'David',
			'surname' => 'Bingham',
			'address' => array(
				'postcode' => 'AB34 5FG'
			)
		);

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$this->setExpectedException(
			'Sloth\Exception\InvalidRequestException',
			'No filters given for table: User_address'
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

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$firstQuerySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $firstQuerySet);
		$this->assertEquals(1, $firstQuerySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $firstQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLink', $firstChildLink);
		$this->assertSame($table->links->getByName('posts'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var MultiQueryWrapperInterface $secondQuerySet */
		$secondQuerySet = $firstChildLink->getChildQueryWrapper();
		$this->assertSame($secondQuerySet, $firstChildLink->getChildQueryWrapper());
		$this->assertEquals(2, $secondQuerySet->length());

		/** @var MultiQueryWrapperInterface $secondQuerySetFirstSubset */
		$secondQuerySetFirstSubset = $secondQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondQuerySetFirstSubset);
		$this->assertEquals(1, $secondQuerySetFirstSubset->length());
		$this->assertNull($secondQuerySetFirstSubset->getChildLinks());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $secondQuerySetFirstSubset->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());
		$this->assertSame($postTable, $secondQueryWrapper->getTable());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $secondQuery);
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());

		/** @var MultiQueryWrapperInterface $secondQuerySetSecondSubset */
		$secondQuerySetSecondSubset = $secondQuerySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondQuerySetSecondSubset);
		$this->assertEquals(1, $secondQuerySetSecondSubset->length());
		$this->assertNull($secondQuerySetSecondSubset->getChildLinks());

		/** @var SingleQueryWrapperInterface $thirdQueryWrapper */
		$thirdQueryWrapper = $secondQuerySetSecondSubset->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $thirdQueryWrapper);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $thirdQueryWrapper->getChildLinks());
		$this->assertSame($postTable, $thirdQueryWrapper->getTable());

		$thirdQuery = $thirdQueryWrapper->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $thirdQuery);
		$this->assertEquals($expectedQueries[2], (string)$thirdQuery);
		$this->assertEquals(0, $thirdQueryWrapper->getChildLinks()->length());
	}

	public function testQueriesToUpdateOneToManyLinkedTableIncludesExistingDataForLinkFieldWhenNoNewDataIsProvided()
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
SET `Post`.`content` = "Second updated post",
	`Post`.`authorId` = 7
WHERE `Post`.`id` = 13
EOT;

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$firstQuerySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $firstQuerySet);
		$this->assertEquals(1, $firstQuerySet->length());

		/** @var SingleQueryWrapperInterface $firstQueryWrapper */
		$firstQueryWrapper = $firstQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $firstQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstQueryWrapper->getChildLinks());

		$firstQuery = $firstQueryWrapper->getQuery();
		$this->assertSame($table, $firstQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$firstQuery);
		$this->assertEquals(1, $firstQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $firstChildLink */
		$firstChildLink = $firstQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLink', $firstChildLink);
		$this->assertSame($table->links->getByName('posts'), $firstChildLink->getJoinDefinition());
		$this->assertSame($firstQueryWrapper, $firstChildLink->getParentQueryWrapper());

		/** @var MultiQueryWrapperInterface $secondQuerySet */
		$secondQuerySet = $firstChildLink->getChildQueryWrapper();
		$this->assertSame($secondQuerySet, $firstChildLink->getChildQueryWrapper());
		$this->assertEquals(2, $secondQuerySet->length());

		/** @var MultiQueryWrapperInterface $secondQuerySetFirstSubset */
		$secondQuerySetFirstSubset = $secondQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondQuerySetFirstSubset);
		$this->assertEquals(1, $secondQuerySetFirstSubset->length());
		$this->assertNull($secondQuerySetFirstSubset->getChildLinks());

		/** @var SingleQueryWrapperInterface $secondQueryWrapper */
		$secondQueryWrapper = $secondQuerySetFirstSubset->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondQueryWrapper);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondQueryWrapper->getChildLinks());
		$this->assertSame($postTable, $secondQueryWrapper->getTable());

		$secondQuery = $secondQueryWrapper->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $secondQuery);
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $secondQueryWrapper->getChildLinks()->length());

		/** @var MultiQueryWrapperInterface $secondQuerySetSecondSubset */
		$secondQuerySetSecondSubset = $secondQuerySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondQuerySetSecondSubset);
		$this->assertEquals(1, $secondQuerySetSecondSubset->length());
		$this->assertNull($secondQuerySetSecondSubset->getChildLinks());

		/** @var SingleQueryWrapperInterface $thirdQueryWrapper */
		$thirdQueryWrapper = $secondQuerySetSecondSubset->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $thirdQueryWrapper);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $thirdQueryWrapper->getChildLinks());
		$this->assertSame($postTable, $thirdQueryWrapper->getTable());

		$thirdQuery = $thirdQueryWrapper->getQuery();
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $thirdQuery);
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
					'id' => 12,
					'content' => 'First updated post'
				),
				array(
					'id' => 13,
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
SET `UserAddress`.`postcode` = "AB34 5FG"
WHERE `UserAddress`.`userId` = 7
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Post`
SET `Post`.`id` = 12,
	`Post`.`content` = "First updated post",
	`Post`.`authorId` = 7
WHERE `Post`.`id` = 12
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Post`
SET `Post`.`id` = 13,
	`Post`.`content` = "Second updated post",
	`Post`.`authorId` = 7
WHERE `Post`.`id` = 13
EOT;

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $userUpdateQueryWrapper */
		$userUpdateQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $userUpdateQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $userUpdateQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $userUpdateQueryWrapper->getChildLinks());

		$userUpdateQuery = $userUpdateQueryWrapper->getQuery();
		$this->assertSame($table, $userUpdateQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$userUpdateQuery);
		$this->assertEquals(2, $userUpdateQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $linkToAddressUpdate */
		$linkToAddressUpdate = $userUpdateQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $linkToAddressUpdate);
		$this->assertSame($table->links->getByName('address'), $linkToAddressUpdate->getJoinDefinition());

		/** @var QueryLinkInterface $linkToPostUpdate */
		$linkToPostUpdate = $userUpdateQueryWrapper->getChildLinks()->getByIndex(1);
		$this->assertInstanceof('Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $linkToPostUpdate);
		$this->assertSame($table->links->getByName('posts'), $linkToPostUpdate->getJoinDefinition());

		/** @var SingleQueryWrapperInterface $addressUpdateQueryWrapper */
		$addressUpdateQueryWrapper = $linkToAddressUpdate->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $addressUpdateQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $addressUpdateQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $addressUpdateQueryWrapper->getChildLinks());

		/** @var MultiQueryWrapperInterface $postUpdateQuerySet */
		$postUpdateQuerySet = $linkToPostUpdate->getChildQueryWrapper();
		$this->assertEquals(2, $postUpdateQuerySet->length());

		/** @var MultiQueryWrapperInterface $firstPostUpdateQuerySubSet */
		$firstPostUpdateQuerySubSet = $postUpdateQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $firstPostUpdateQuerySubSet);
		$this->assertEquals(1, $firstPostUpdateQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $firstPostUpdateQueryWrapper */
		$firstPostUpdateQueryWrapper = $firstPostUpdateQuerySubSet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $firstPostUpdateQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $firstPostUpdateQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstPostUpdateQueryWrapper->getChildLinks());

		/** @var MultiQueryWrapperInterface $secondPostUpdateQuerySubSet */
		$secondPostUpdateQuerySubSet = $postUpdateQuerySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondPostUpdateQuerySubSet);
		$this->assertEquals(1, $secondPostUpdateQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $secondPostUpdateQueryWrapper */
		$secondPostUpdateQueryWrapper = $secondPostUpdateQuerySubSet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $secondPostUpdateQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $secondPostUpdateQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondPostUpdateQueryWrapper->getChildLinks());

		$addressUpdateQuery = $addressUpdateQueryWrapper->getQuery();
		$this->assertSame($addressTable, $addressUpdateQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$addressUpdateQuery);
		$this->assertEquals(0, $addressUpdateQueryWrapper->getChildLinks()->length());

		$firstPostUpdateQuery = $firstPostUpdateQueryWrapper->getQuery();
		$this->assertSame($postTable, $firstPostUpdateQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[2], (string)$firstPostUpdateQuery);
		$this->assertEquals(0, $firstPostUpdateQueryWrapper->getChildLinks()->length());

		$secondPostUpdateQuery = $secondPostUpdateQueryWrapper->getQuery();
		$this->assertSame($postTable, $secondPostUpdateQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[3], (string)$secondPostUpdateQuery);
		$this->assertEquals(0, $secondPostUpdateQueryWrapper->getChildLinks()->length());
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
							'id' => 21,
							'content' => 'First reply to first post'
						),
						array(
							'id' => 22,
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
SET `Comment`.`id` = 21,
	`Comment`.`content` = "First reply to first post",
	`Comment`.`postId` = 11
WHERE `Comment`.`id` = 21
EOT;
		$expectedQueries[] = <<<EOT
UPDATE `Comment`
SET `Comment`.`id` = 22,
	`Comment`.`content` = "Second reply to first post",
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

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $updateUserQueryWrapper */
		$updateUserQueryWrapper = $querySet->getByIndex(0);
		$updateUserChildLinks = $updateUserQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $updateUserQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $updateUserQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $updateUserChildLinks);
		$this->assertSame($table, $updateUserQueryWrapper->getTable());
		$this->assertEquals(1, $updateUserChildLinks->length());

		/** @var QueryLinkInterface $linkToUpdatePostsQuerySet */
		$linkToUpdatePostsQuerySet = $updateUserQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $linkToUpdatePostsQuerySet);
		$this->assertSame($table->links->getByName('posts'), $linkToUpdatePostsQuerySet->getJoinDefinition());

		/** @var MultiQueryWrapperInterface $updatePostsQuerySetWrapper */
		$updatePostsQuerySetWrapper = $linkToUpdatePostsQuerySet->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $updatePostsQuerySetWrapper);
		$this->assertEquals(2, $updatePostsQuerySetWrapper->length());

		/** @var MultiQueryWrapperInterface $firstPostQuerySubset */
		$firstPostQuerySubset = $updatePostsQuerySetWrapper->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $firstPostQuerySubset);
		$this->assertEquals(1, $firstPostQuerySubset->length());

		/** @var SingleQueryWrapperInterface $updateFirstPostQueryWrapper */
		$updateFirstPostQueryWrapper = $firstPostQuerySubset->getByIndex(0);
		$firstPostChildLinks = $updateFirstPostQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $updateFirstPostQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $updateFirstPostQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstPostChildLinks);
		$this->assertSame($postTable, $updateFirstPostQueryWrapper->getTable());
		$this->assertEquals(1, $firstPostChildLinks->length());

		/** @var QueryLinkInterface $linkToUpdateFirstPostComments */
		$linkToUpdateFirstPostComments = $firstPostChildLinks->getByIndex(0);

		/** @var MultiQueryWrapperInterface $updateFirstPostCommentsQuerySet */
		$updateFirstPostCommentsQuerySet = $linkToUpdateFirstPostComments->getChildQueryWrapper();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $updateFirstPostCommentsQuerySet);

		/** @var MultiQueryWrapperInterface $firstCommentOnFirstPostQuerySubSet */
		$firstCommentOnFirstPostQuerySubSet = $updateFirstPostCommentsQuerySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $firstCommentOnFirstPostQuerySubSet);
		$this->assertEquals(1, $firstCommentOnFirstPostQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $updateFirstCommentQueryWrapper */
		$updateFirstCommentQueryWrapper = $firstCommentOnFirstPostQuerySubSet->getByIndex(0);
		$firstCommentChildLinks = $updateFirstCommentQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $updateFirstCommentQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $updateFirstCommentQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $firstCommentChildLinks);
		$this->assertSame($commentTable, $updateFirstCommentQueryWrapper->getTable());
		$this->assertEquals(0, $firstCommentChildLinks->length());

		/** @var MultiQueryWrapperInterface $secondCommentOnFirstPostQuerySubSet */
		$secondCommentOnFirstPostQuerySubSet = $updateFirstPostCommentsQuerySet->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondCommentOnFirstPostQuerySubSet);
		$this->assertEquals(1, $secondCommentOnFirstPostQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $updateSecondCommentQueryWrapper */
		$updateSecondCommentQueryWrapper = $secondCommentOnFirstPostQuerySubSet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $updateSecondCommentQueryWrapper);
		$secondCommentChildLinks = $updateSecondCommentQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $updateSecondCommentQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $updateSecondCommentQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondCommentChildLinks);
		$this->assertSame($commentTable, $updateSecondCommentQueryWrapper->getTable());
		$this->assertEquals(0, $secondCommentChildLinks->length());

		/** @var MultiQueryWrapperInterface $secondPostQuerySubSet */
		$secondPostQuerySubSet = $updatePostsQuerySetWrapper->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $secondPostQuerySubSet);
		$this->assertEquals(1, $secondPostQuerySubSet->length());

		/** @var SingleQueryWrapperInterface $updateSecondPostQueryWrapper */
		$updateSecondPostQueryWrapper = $secondPostQuerySubSet->getByIndex(0);
		$secondPostChildLinks = $updateSecondPostQueryWrapper->getChildLinks();
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $updateSecondPostQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $updateSecondPostQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $secondPostChildLinks);
		$this->assertSame($postTable, $updateSecondPostQueryWrapper->getTable());
		$this->assertEquals(0, $secondPostChildLinks->length());

		$updateUserQuery = $updateUserQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[0], (string)$updateUserQuery);

		$updateFirstPostQuery = $updateFirstPostQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[1], (string)$updateFirstPostQuery);

		$updateFirstCommentQuery = $updateFirstCommentQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[2], (string)$updateFirstCommentQuery);

		$updateSecondCommentQuery = $updateSecondCommentQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[3], (string)$updateSecondCommentQuery);

		$updateSecondPostQuery = $updateSecondPostQueryWrapper->getQuery();
		$this->assertEquals($expectedQueries[4], (string)$updateSecondPostQuery);
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

		$composer = new UpdateComposer();
		$composer->setDatabase($database)
			->setTable($table)
			->setFilters($filters)
			->setData($data);

		$querySet = $composer->compose();

		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper', $querySet);
		$this->assertEquals(1, $querySet->length());

		/** @var SingleQueryWrapperInterface $updateUserQueryWrapper */
		$updateUserQueryWrapper = $querySet->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $updateUserQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Update', $updateUserQueryWrapper->getQuery());
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList', $updateUserQueryWrapper->getChildLinks());

		$updateUserQuery = $updateUserQueryWrapper->getQuery();
		$this->assertSame($table, $updateUserQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[0], (string)$updateUserQuery);
		$this->assertEquals(1, $updateUserQueryWrapper->getChildLinks()->length());

		/** @var QueryLinkInterface $linkToUpdateFriendsQuerySet */
		$linkToUpdateFriendsQuerySet = $updateUserQueryWrapper->getChildLinks()->getByIndex(0);
		$this->assertInstanceof('Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface', $linkToUpdateFriendsQuerySet);
		$this->assertSame($table->links->getByName('friends'), $linkToUpdateFriendsQuerySet->getJoinDefinition());

		/** @var MultiQueryWrapperInterface $updateFriendsQuerySetWrapper */
		$updateFriendsQuerySetWrapper = $linkToUpdateFriendsQuerySet->getChildQueryWrapper();
		$this->assertEquals(2, $updateFriendsQuerySetWrapper->length());

		/** @var SingleQueryWrapperInterface $updateFirstFriendQueryWrapper */
		$updateFirstFriendQueryWrapper = $updateFriendsQuerySetWrapper->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $updateFirstFriendQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Delete', $updateFirstFriendQueryWrapper->getQuery());
		$this->assertEquals(0, $updateFirstFriendQueryWrapper->getChildLinks()->length());

		/** @var SingleQueryWrapperInterface $updateSecondFriendQueryWrapper */
		$updateSecondFriendQueryWrapper = $updateFriendsQuerySetWrapper->getByIndex(1);
		$this->assertInstanceOf('Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper', $updateSecondFriendQueryWrapper);
		$this->assertInstanceOf('SlothMySql\QueryBuilder\Query\Insert', $updateSecondFriendQueryWrapper->getQuery());
		$this->assertEquals(0, $updateSecondFriendQueryWrapper->getChildLinks()->length());

		$secondQuery = $updateFirstFriendQueryWrapper->getQuery();
		$this->assertSame($friendLink->intermediaryTables->getByIndex(0), $updateFirstFriendQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[1], (string)$secondQuery);
		$this->assertEquals(0, $updateFirstFriendQueryWrapper->getChildLinks()->length());

		$thirdQuery = $updateSecondFriendQueryWrapper->getQuery();
		$this->assertSame($friendLink->intermediaryTables->getByIndex(0), $updateSecondFriendQueryWrapper->getTable());
		$this->assertEquals($expectedQueries[2], (string)$thirdQuery);
		$this->assertEquals(0, $updateSecondFriendQueryWrapper->getChildLinks()->length());
	}
}
