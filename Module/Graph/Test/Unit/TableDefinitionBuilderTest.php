<?php
namespace Sloth\Module\Graph\Test;

require_once dirname(__DIR__) . '/UnitTest.php';

use DemoGraph\Test\UnitTest;

class TableDefinitionBuilderTest extends UnitTest
{
	use Assertions\ResourceBuilderAssertions;

	public function testTableCanBeBuiltFromNamedManifestFile()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();
		$resource = $resourceDefinitionBuilder->buildFromName('user');

		$this->assertBuiltTableMatchesUserManifest($resource);
		$this->assertBuiltUserTableJoinsToFriendsTable($resource);
		$this->assertBuiltUserTableJoinsToPostsTable($resource);
        $this->assertBuiltUserTableJoinsToAddressSubTable($resource);
		$this->assertBuildPostsTableJoinsToCommentsTable($resource->links->getByName('posts')->getChildTable());
	}

	public function testConnectedTablesAreLoadedOnDemand()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();
		$resource = $resourceDefinitionBuilder->buildFromName('user');

		$friendTable = $resource->links->getByName('friends')->getChildTable();
		$postTable = $resource->links->getByName('posts')->getChildTable();

		$this->assertNotSame($resource, $friendTable);
		$this->assertBuiltTableMatchesUserManifest($friendTable);
		$this->assertBuiltTableMatchesPostManifest($postTable);
		$this->assertBuiltPostTableJoinsToAuthorTable($postTable);
		$this->assertBuiltTableMatchesUserManifest($postTable->links->getByName('author')->getChildTable());
	}

	public function testConnectedTablesHaveUniqueAliases()
    {

    }
}
