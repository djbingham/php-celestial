<?php
namespace DemoGraph\Module\Graph\Test;

require_once dirname(__DIR__) . '/UnitTest.php';

use DemoGraph\Module\Graph\Factory;
use DemoGraph\Module\Graph\ResourceManifestValidator;
use DemoGraph\Test\UnitTest;
use Sloth\App;

class IntegrationTest extends UnitTest
{
	use Assertions\ResourceBuilderAssertions;

	public function testResourceCanBeBuiltFromNamedManifestFileUsingDefaultSubBuilders()
	{
		$factory = new Factory($this->mockApp());
		$manifestValidator = new ResourceManifestValidator();
		$manifestDirectory = dirname(__DIR__) . '/sample/resourceManifest';
		$resourceBuilder = $factory->resourceDefinitionBuilder($manifestValidator, $manifestDirectory);

		$resource = $resourceBuilder->buildFromName('user');

		$this->assertBuiltResourceMatchesUserManifest($resource);
		$this->assertBuiltUserResourceLinksToFriendsSubResource($resource);
		$this->assertBuiltUserResourceLinksToPostsSubResource($resource);
	}

	public function testConnectedResourcesAreLoadedOnDemand()
	{
		$factory = new Factory($this->mockApp());
		$manifestValidator = new ResourceManifestValidator();
		$manifestDirectory = dirname(__DIR__) . '/sample/resourceManifest';
		$resourceBuilder = $factory->resourceDefinitionBuilder($manifestValidator, $manifestDirectory);

		$resource = $resourceBuilder->buildFromName('user');

		$friendResource = $resource->links->getByName('friends')->getChildResource();
		$postResource = $resource->links->getByName('posts')->getChildResource();

		$this->assertNotSame($resource, $friendResource);
		$this->assertBuiltResourceMatchesUserManifest($friendResource);
		$this->assertBuiltResourceMatchesPostManifest($postResource);
		$this->assertBuiltPostResourceLinksToAuthorSubResource($postResource);
		$this->assertBuiltResourceMatchesUserManifest($postResource->links->getByName('author')->getChildResource());
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
