<?php
namespace Sloth\Module\Graph\Test;

require_once dirname(__DIR__) . '/UnitTest.php';

use Sloth\Module\Graph\DefinitionBuilder\TableDefinitionBuilder;
use Sloth\Module\Graph\TableManifestValidator;
use DemoGraph\Test\UnitTest;
use Sloth\Module\Graph\DefinitionBuilder\TableFieldBuilder;
use Sloth\Module\Graph\DefinitionBuilder\TableFieldListBuilder;
use Sloth\Module\Graph\DefinitionBuilder\LinkListBuilder;
use Sloth\Module\Graph\DefinitionBuilder\TableBuilder;
use Sloth\Module\Graph\DefinitionBuilder\ValidatorListBuilder;
use Sloth\Module\Graph\DefinitionBuilder\ViewListBuilder;

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
