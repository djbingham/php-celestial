<?php
namespace DemoGraph\Module\Graph\Test;

require_once dirname(__DIR__) . '/UnitTest.php';

use DemoGraph\Module\Graph\DefinitionBuilder\ResourceDefinitionBuilder;
use DemoGraph\Module\Graph\ResourceManifestValidator;
use DemoGraph\Test\UnitTest;
use DemoGraph\Module\Graph\DefinitionBuilder\AttributeBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\AttributeListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\LinkListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\TableBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ValidatorListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ViewListBuilder;

class ResourceDefinitionBuilderTest extends UnitTest
{
	use Assertions\ResourceBuilderAssertions;

	public function testResourceCanBeBuiltFromNamedManifestFile()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$resource = $resourceDefinitionBuilder->buildFromName('user');

		$this->assertBuiltResourceMatchesUserManifest($resource);
		$this->assertBuiltUserResourceLinksToFriendsSubResource($resource);
		$this->assertBuiltUserResourceLinksToPostsSubResource($resource);
        $this->assertBuiltUserResourceLinksToAddressSubResource($resource);
	}

	public function testConnectedResourcesAreLoadedOnDemand()
	{
		$resourceDefinitionBuilder = $this->getResourceDefinitionBuilder();
		$resource = $resourceDefinitionBuilder->buildFromName('user');

		$friendResource = $resource->links->getByName('friends')->getChildResource();
		$postResource = $resource->links->getByName('posts')->getChildResource();

		$this->assertNotSame($resource, $friendResource);
		$this->assertBuiltResourceMatchesUserManifest($friendResource);
		$this->assertBuiltResourceMatchesPostManifest($postResource);
		$this->assertBuiltPostResourceLinksToAuthorSubResource($postResource);
		$this->assertBuiltResourceMatchesUserManifest($postResource->links->getByName('author')->getChildResource());
	}

	public function testConnectedResourcesHaveUniqueAliases()
    {

    }
}
