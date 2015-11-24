<?php
namespace Sloth\Module\Resource\Test\Unit;

require_once dirname(__DIR__) . '/UnitTest.php';

use Sloth\App;
use Sloth\Module\Resource\ResourceModule;
use Sloth\Module\Resource\ResourceManifestValidator;
use Sloth\Module\Resource\Test\Assertions;
use Sloth\Module\Resource\Test\UnitTest;

class ResourceDefinitionBuilderTest extends UnitTest
{
	use Assertions\TableDefinitionAssertions;

	public function testTableCanBeBuiltFromNamedManifestFileUsingDefaultSubBuilders()
	{
		$factory = new ResourceModule($this->mockApp());
		$resourceManifestValidator = new ResourceManifestValidator();
		$resourceManifestDirectory = dirname(__DIR__) . '/sample/resourceManifest';
		$factory->setResourceManifestValidator($resourceManifestValidator)
			->setResourceManifestDirectory($resourceManifestDirectory);
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
		$factory = new ResourceModule($this->mockApp());
		$resourceManifestValidator = new ResourceManifestValidator();
		$resourceManifestDirectory = dirname(__DIR__) . '/sample/resourceManifest';
		$factory->setResourceManifestValidator($resourceManifestValidator)
			->setResourceManifestDirectory($resourceManifestDirectory);
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
