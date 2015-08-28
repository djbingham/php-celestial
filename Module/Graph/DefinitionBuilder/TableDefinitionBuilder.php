<?php
namespace Sloth\Module\Graph\DefinitionBuilder;

use Sloth\Module\Graph\Definition;
use Sloth\Module\Graph\TableManifestValidator;

class TableDefinitionBuilder
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
	 * @var ViewListBuilder
	 */
	private $viewListBuilder;

	/**
	 * @var ValidatorListBuilder
	 */
	private $validatorListBuilder;

	/**
	 * @var TableManifestValidator
	 */
	private $manifestValidator;

	/**
	 * @var string
	 */
	private $manifestDirectory;

	public function __construct(TableManifestValidator $manifestValidator, $manifestDirectory)
	{
		$this->manifestValidator = $manifestValidator;
		$this->manifestDirectory = $manifestDirectory;
	}

	public function setSubBuilders(array $builders)
	{
		$this->validatorListBuilder = $builders['validatorListBuilder'];
		$this->tableFieldListBuilder = $builders['tableFieldListBuilder'];
		$this->linkListBuilder = $builders['linkListBuilder'];
		$this->viewListBuilder = $builders['viewListBuilder'];
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
		$manifest = json_decode($fileContents, true);
		$manifest['name'] = ucfirst($fileName);
		return $this->buildFromManifest($manifest, $alias);
	}

	public function buildFromManifest(array $manifest, $alias = null)
	{
		$manifest = $this->padManifest($manifest);
		$this->assertManifestIsValid($manifest);

		$table = new Definition\Table();
		if (!is_null($alias)) {
			$table->alias = $alias;
		} else {
			$table->alias = $manifest['name'];
		}
		$table->name = $manifest['name'];
		$table->fields = $this->tableFieldListBuilder->build($table, $manifest['fields']);
		$table->links = $this->linkListBuilder->build($table, $manifest['links']);
		$table->validators = $this->validatorListBuilder->build($manifest['validators']);
		$table->views = $this->viewListBuilder->build($manifest['views']);

		return $table;
	}

	private function assertManifestFileExists($filePath)
	{
		if (!is_file($filePath)) {
			throw new \Exception('Manifest file not found: ' . $filePath);
		}
	}

	private function assertManifestIsValid(array $manifest)
	{
		if (!$this->manifestValidator->validate($manifest)) {
			$errorString = implode('; ', $this->manifestValidator->getErrors());
			throw new \Exception('Manifest file failed validation, with the following errors: ' . $errorString);
		}
	}

	private function padManifest(array $manifest)
	{
		if (!array_key_exists('fields', $manifest)) {
			$manifest['fields'] = array();
		}
		if (!array_key_exists('links', $manifest)) {
			$manifest['links'] = array();
		}
		if (!array_key_exists('views', $manifest)) {
			$manifest['views'] = array();
		}
		if (!array_key_exists('validators', $manifest)) {
			$manifest['validators'] = array();
		}
		return $manifest;
	}
}