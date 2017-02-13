<?php
namespace Celestial\Module\Data\Resource\Test\Unit;

require_once dirname(__DIR__) . '/UnitTest.php';

use Celestial\App;
use Celestial\Module\Data\Resource\DefinitionBuilder\ResourceDefinitionBuilder;
use Celestial\Module\Data\Resource\Test\UnitTest;

class ResourceDefinitionBuilderTest extends UnitTest
{
	public function testBuildFromNameBuildsResourceUsingManifestFile()
	{
		$definitionBuilder = new ResourceDefinitionBuilder($this->mockApp());

		$manifestDirectory = dirname(__DIR__) . '/sample/resourceManifest';
		$manifestValidator = $this->mockManifestValidator();
		$tableModule = $this->mockTableModule();
		$validatorListBuilder = $this->mockValidatorListBuilder();
		$attributeListBuilder = $this->mockAttributeListBuilder();

		$mockTableDefinition = $this->mockTableDefinition();
		$mockAttributeList = $this->mockAttributeList();
		$mockValidatorList = $this->mockValidatorList();

		$manifestValidator->expects($this->once())
				->method('validate')
				->willReturn(true);

		$tableModule->expects($this->once())
				->method('get')
				->with('User')
				->will($this->returnValue($mockTableDefinition));

		$attributeListBuilder->expects($this->once())
			->method('build')
			->will($this->returnValue($mockAttributeList));

		$validatorListBuilder->expects($this->once())
			->method('build')
			->with(array())
			->will($this->returnValue($mockValidatorList));

		$definitionBuilder->setManifestValidator($manifestValidator)
			->setManifestDirectory($manifestDirectory)
			->setTableModule($tableModule)
			->setSubBuilders(array(
				'validatorListBuilder' => $validatorListBuilder,
				'attributeListBuilder' => $attributeListBuilder
			));

		$resource = $definitionBuilder->buildFromName('user');

		$this->assertEquals('User', $resource->name);
		$this->assertEquals('id', $resource->primaryAttribute);
		$this->assertEquals($mockTableDefinition, $resource->table);
		$this->assertEquals($mockAttributeList, $resource->attributes);
		$this->assertEquals($mockValidatorList, $resource->validators);
	}

	public function testBuildFromManifest()
	{
		$definitionBuilder = new ResourceDefinitionBuilder($this->mockApp());

		$manifest = (object)array(
			'name' => "User",
			'table' => "User",
			'primaryAttribute' => "id",
			'attributes' => (object)array(
				'id' => true,
				'forename' => true
			)
		);

		$manifestValidator = $this->mockManifestValidator();
		$tableModule = $this->mockTableModule();
		$validatorListBuilder = $this->mockValidatorListBuilder();
		$attributeListBuilder = $this->mockAttributeListBuilder();

		$mockTableDefinition = $this->mockTableDefinition();
		$mockAttributeList = $this->mockAttributeList();
		$mockValidatorList = $this->mockValidatorList();

		$manifestValidator->expects($this->once())
			->method('validate')
			->willReturn(true);

		$tableModule->expects($this->once())
			->method('get')
			->with('User')
			->will($this->returnValue($mockTableDefinition));

		$attributeListBuilder->expects($this->once())
			->method('build')
			->will($this->returnValue($mockAttributeList));

		$attributeListBuilder->expects($this->once())
			->method('build')
			->will($this->returnValue($mockAttributeList));

		$validatorListBuilder->expects($this->once())
			->method('build')
			->with(array())
			->will($this->returnValue($mockValidatorList));

		$definitionBuilder->setManifestValidator($manifestValidator)
			->setTableModule($tableModule)
			->setSubBuilders(array(
				'validatorListBuilder' => $validatorListBuilder,
				'attributeListBuilder' => $attributeListBuilder
			));

		$resource = $definitionBuilder->buildFromManifest($manifest);

		$this->assertEquals('User', $resource->name);
		$this->assertEquals('id', $resource->primaryAttribute);
		$this->assertEquals($mockTableDefinition, $resource->table);
		$this->assertEquals($mockAttributeList, $resource->attributes);
		$this->assertEquals($mockValidatorList, $resource->validators);
	}

	/**
	 * @return App
	 */
	private function mockApp()
	{
		$app = $this->getMockBuilder('Celestial\App')
			->disableOriginalConstructor()
			->getMock();
		$app->expects($this->any())
			->method('database')
			->will($this->returnValue($this->getDatabaseWrapper()));
		return $app;
	}

	protected function mockTableModule()
	{
		return $this->getMockBuilder('Celestial\Module\Data\Table\TableModule')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockManifestValidator()
	{
		return $this->getMockBuilder('Celestial\Module\Data\Resource\ResourceManifestValidator')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockValidatorListBuilder()
	{
		return $this->getMockBuilder('Celestial\Module\Data\Resource\DefinitionBuilder\ValidatorListBuilder')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockAttributeListBuilder()
	{
		return $this->getMockBuilder('Celestial\Module\Data\Resource\DefinitionBuilder\AttributeListBuilder')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockTableDefinition()
	{
		return $this->getMockBuilder('Celestial\Module\Data\Table\Definition\Table')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockAttributeList()
	{
		return $this->getMockBuilder('Celestial\Module\Data\Resource\Definition\Resource\AttributeList')
				->disableOriginalConstructor()
				->getMock();
	}

	protected function mockValidatorList()
	{
		return $this->getMockBuilder('Celestial\Module\Data\Resource\Definition\Resource\ValidatorList')
				->disableOriginalConstructor()
				->getMock();
	}
}
