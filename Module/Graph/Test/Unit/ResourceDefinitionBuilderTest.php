<?php
namespace Sloth\Module\Graph\Test;

require_once dirname(__DIR__) . '/UnitTest.php';

use Sloth\Module\Graph\ModuleCore;
use Sloth\Module\Graph\ResourceManifestValidator;
use DemoGraph\Test\UnitTest;
use Sloth\App;
use Sloth\Module\Graph\TableManifestValidator;

class IntegrationTest extends UnitTest
{
	use Assertions\ResourceBuilderAssertions;

	public function testTableCanBeBuiltFromNamedManifestFileUsingDefaultSubBuilders()
	{
		$factory = new ModuleCore($this->mockApp());
		$resourceManifestValidator = new ResourceManifestValidator();
		$resourceManifestDirectory = dirname(__DIR__) . '/sample/resourceManifest';
		$tableManifestValidator = new TableManifestValidator();
		$tableManifestDirectory = dirname(__DIR__) . '/sample/tableManifest';
		$factory->setResourceManifestValidator($resourceManifestValidator)
			->setResourceManifestDirectory($resourceManifestDirectory)
			->setTableManifestValidator($tableManifestValidator)
			->setTableManifestDirectory($tableManifestDirectory);
		$resourceBuilder = $factory->resourceDefinitionBuilder();

		$resource = $resourceBuilder->buildFromName('user');
		$table = $resource->table;

		$this->assertBuiltTableMatchesUserManifest($table);
		$this->assertBuiltUserTableJoinsToFriendsTable($table);
		$this->assertBuiltUserTableJoinsToPostsTable($table);
		$this->assertBuildPostsTableJoinsToCommentsTable($table->links->getByName('posts')->getChildTable());
	}

	public function testConnectedTablesAreLoadedOnDemand()
	{
		$factory = new ModuleCore($this->mockApp());
		$resourceManifestValidator = new ResourceManifestValidator();
		$resourceManifestDirectory = dirname(__DIR__) . '/sample/resourceManifest';
		$tableManifestValidator = new TableManifestValidator();
		$tableManifestDirectory = dirname(__DIR__) . '/sample/tableManifest';
		$factory->setResourceManifestValidator($resourceManifestValidator)
			->setResourceManifestDirectory($resourceManifestDirectory)
			->setTableManifestValidator($tableManifestValidator)
			->setTableManifestDirectory($tableManifestDirectory);
		$resourceBuilder = $factory->resourceDefinitionBuilder();

		$resource = $resourceBuilder->buildFromName('user');
		$table = $resource->table;

		$friendTable = $table->links->getByName('friends')->getChildTable();
		$postTable = $table->links->getByName('posts')->getChildTable();

		$this->assertNotSame($table, $friendTable);
		$this->assertBuiltTableMatchesUserManifest($friendTable);
		$this->assertBuiltTableMatchesPostManifest($postTable);
		$this->assertBuiltPostTableJoinsToAuthorTable($postTable);
		$this->assertBuiltTableMatchesUserManifest($postTable->links->getByName('author')->getChildTable());
	}

	/**
	 * @return App
	 */
	private function mockApp()
	{
		$app = $this->getMockBuilder('Sloth\App')
			->disableOriginalConstructor()
			->getMock();
		$app->expects($this->any())
			->method('database')
			->will($this->returnValue($this->getDatabaseWrapper()));
		return $app;
	}
}
