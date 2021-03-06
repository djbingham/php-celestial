<?php
namespace Celestial\Module\Data\Table;

use Celestial\App;
use Celestial\Module\Data\Table\DefinitionBuilder\TableBuilder;

class TableModule
{
	/**
	 * @var App
	 */
	private $app;

	/**
	 * @var string
	 */
	private $tableManifestDirectory;

	/**
	 * @var TableBuilder
	 */
	private $tableBuilder;

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function setTableManifestDirectory($directory)
	{
		$this->tableManifestDirectory = $directory;
		return $this;
	}

	public function getTableManifestDirectory()
	{
		return $this->tableManifestDirectory;
	}

	public function setTableBuilder(TableBuilder $tableBuilder)
	{
		$this->tableBuilder = $tableBuilder;
		return $this;
	}

	public function build(\stdClass $tableManifest)
	{
		return $this->tableBuilder->buildFromManifest($tableManifest);
	}

	public function exists($tableName)
	{
		$manifestFilePath = $this->getManifestPath($tableName);

		return is_file($manifestFilePath);
	}

	public function get($tableName)
	{
		$table = null;
		$manifestPath = $this->getManifestPath($tableName);

		if (is_file($manifestPath)) {
			$table = $this->tableBuilder->buildFromFile($manifestPath);
		}

		return $table;
	}

	public function getManifestPath($tableName)
	{
		$pathParts = explode('/', $tableName);

		foreach ($pathParts as &$pathPart) {
			$pathPart = ucfirst($pathPart);
		}

		return $this->tableManifestDirectory . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $pathParts) . '.json';
	}
}
