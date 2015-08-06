<?php
namespace DemoGraph\Module\Graph\Test;

require_once dirname(__DIR__) . '/UnitTest.php';

use DemoGraph\Module\Graph\DefinitionBuilder\TableDefinitionBuilder;
use DemoGraph\Module\Graph\TableManifestValidator;
use DemoGraph\Test\UnitTest;
use DemoGraph\Module\Graph\DefinitionBuilder\TableFieldBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\TableFieldListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\LinkListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\TableBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ValidatorListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ViewListBuilder;

class ResourceDefinitionBuilderTest extends UnitTest
{
	use Assertions\ResourceBuilderAssertions;

	public function testResourceCanBeBuiltFromNamedManifestFile()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();
		$resource = $resourceDefinitionBuilder->buildFromName('user');

		$this->assertBuiltResourceMatchesUserManifest($resource);
		$this->assertBuiltUserResourceLinksToFriendsSubResource($resource);
		$this->assertBuiltUserResourceLinksToPostsSubResource($resource);
        $this->assertBuiltUserResourceLinksToAddressSubResource($resource);
	}

	public function testConnectedResourcesAreLoadedOnDemand()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();
		$resource = $resourceDefinitionBuilder->buildFromName('user');

		$friendResource = $resource->links->getByName('friends')->getChildTable();
		$postResource = $resource->links->getByName('posts')->getChildTable();

		$this->assertNotSame($resource, $friendResource);
		$this->assertBuiltResourceMatchesUserManifest($friendResource);
		$this->assertBuiltResourceMatchesPostManifest($postResource);
		$this->assertBuiltPostResourceLinksToAuthorSubResource($postResource);
		$this->assertBuiltResourceMatchesUserManifest($postResource->links->getByName('author')->getChildTable());
	}

	public function testConnectedResourcesHaveUniqueAliases()
    {

    }
}
