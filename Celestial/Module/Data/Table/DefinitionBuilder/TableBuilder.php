<?php
namespace Celestial\Module\Data\Table\DefinitionBuilder;

use Celestial\Module\Data\Table\Definition;

class TableBuilder
{
	/**
	 * @var TableFieldListBuilder
	 */
	private $tableFieldListBuilder;

	/**
	 * @var LinkListBuilder
	 */
	private $linkListBuilder;

	/**
	 * @var ValidatorListBuilder
	 */
	private $validatorListBuilder;

	/**
	 * @var string
	 */
	private $manifestDirectory;

	public function __construct($manifestDirectory)
	{
		$this->manifestDirectory = $manifestDirectory;
	}

	public function setSubBuilders(array $builders)
	{
		$this->validatorListBuilder = $builders['validatorListBuilder'];
		$this->tableFieldListBuilder = $builders['tableFieldListBuilder'];
		$this->linkListBuilder = $builders['linkListBuilder'];

		return $this;
	}

	public function buildFromName($tableName, $alias = null)
	{
		$filePathParts = explode(DIRECTORY_SEPARATOR, $tableName);
		$lastPathPartIndex = count($filePathParts) - 1;
		$filePathParts[$lastPathPartIndex] = ucfirst($filePathParts[$lastPathPartIndex]);

		$fileName = sprintf('%s.json', implode(DIRECTORY_SEPARATOR, $filePathParts));
		$filePath = $this->manifestDirectory . DIRECTORY_SEPARATOR . $fileName ;

		return $this->buildFromFile($filePath, $alias);
	}

	public function buildFromFile($filePath, $alias = null)
	{
		$this->assertManifestFileExists($filePath);
		$fileContents = file_get_contents($filePath);
		$fileName = basename($filePath, '.json');
		$manifest = json_decode($fileContents);
		$manifest->name = ucfirst($fileName);

		return $this->buildFromManifest($manifest, $alias);
	}

	public function buildFromManifest(\stdClass $manifest, $alias = null)
	{
		$manifest = $this->padManifest($manifest);

		$table = new Definition\Table();
		if (!is_null($alias)) {
			$table->alias = $alias;
		} else {
			$table->alias = $manifest->name;
		}

		$table->name = $manifest->name;
		$table->fields = $this->tableFieldListBuilder->build($table, $manifest->fields);
		$table->links = $this->linkListBuilder->build($table, $manifest->links);
		$table->validators = $this->validatorListBuilder->build($manifest->validators);

		return $table;
	}

	private function assertManifestFileExists($filePath)
	{
		if (!is_file($filePath)) {
			throw new \Exception('Manifest file not found: ' . $filePath);
		}
	}

	private function padManifest(\stdClass $manifest)
	{
		if (!property_exists($manifest, 'fields')) {
			$manifest->fields = new \stdClass();
		}

		if (!property_exists($manifest, 'links')) {
			$manifest->links = new \stdClass();
		}

		if (!property_exists($manifest, 'validators')) {
			$manifest->validators = array();
		}

		return $manifest;
	}
}
