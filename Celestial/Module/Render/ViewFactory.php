<?php
namespace Celestial\Module\Render;

use Celestial\Helper\InternalCacheTrait;
use Celestial\Exception\InvalidArgumentException;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\DataProvider\DataProviderModule;
use Celestial\Module\Render\Face\ViewFactoryInterface;

class ViewFactory implements ViewFactoryInterface
{
	use InternalCacheTrait;

	/**
	 * @var EngineManager
	 */
	private $engineManager;

	/**
	 * @var DataProviderModule
	 */
	private $dataProviderModule;

	/**
	 * @var string
	 */
	private $viewManifestDirectory;

	/**
	 * @var string
	 */
	private $viewDirectory;

	public function __construct(array $dependencies)
	{
		$this->validateDependencies($dependencies);
		$this->engineManager = $dependencies['engineManager'];
		$this->dataProviderModule = $dependencies['dataProviderModule'];
		$this->viewManifestDirectory = $dependencies['viewManifestDirectory'];
		$this->viewDirectory = $dependencies['viewDirectory'];
	}

	public function viewExists($viewPath)
	{
		try {
			$this->getViewManifest($viewPath);
			$found = true;
		} catch (InvalidRequestException $e) {
			$found = false;
		}
		return $found;
	}

	public function getViewManifest($viewPath)
	{
		if ($this->isCached(['view', $viewPath, 'manifest'])) {
			$viewManifest = $this->getCached(['view', $viewPath, 'manifest']);
		} else {
			$manifestPath = $this->getManifestFilePath($viewPath);
			$viewListManifest = $this->getViewListManifest($manifestPath);

			$escapedManifestPath = str_replace('/', '\/', $manifestPath);
			$viewName = ltrim(preg_replace(sprintf('/^%s/', $escapedManifestPath), '', $viewPath), '/');

			if (!array_key_exists($viewName, $viewListManifest)) {
				throw new InvalidRequestException(
					sprintf('View not found with name `%s` in manifest file `%s`', $viewName, $manifestPath)
				);
			}

			$viewManifest = $viewListManifest[$viewName];
			$this->setCached(['view', $viewPath, 'manifest'], $viewManifest);
		}

		return $viewManifest;
	}

	public function getByName($viewName)
	{
		if (!$this->viewExists($viewName)) {
			throw new InvalidArgumentException(sprintf('View not found in manifest: `%s`', $viewName));
		}

		$viewManifest = $this->getViewManifest($viewName);

		return $this->build($viewManifest);
	}

	public function build(array $viewManifest)
	{
		$viewManifest = $this->padViewManifest($viewManifest);

		$view = new \Celestial\Module\Render\View();
		$view->name = $viewManifest['name'];
		$view->path = $this->viewDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $viewManifest['path']);
		$view->engine = $this->engineManager->getByName($viewManifest['engine']);
		$view->dataProviders = $this->buildDataProviders($viewManifest['dataProviders']);
		$view->options = $viewManifest['options'];

		return $view;
	}

	private function validateDependencies(array $dependencies)
	{
		$required = array('engineManager', 'dataProviderModule', 'viewDirectory');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for ViewFactory in Render module: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['engineManager'] instanceof EngineManager)) {
			throw new InvalidArgumentException('Invalid render engine factory given in dependencies for ViewFactory');
		}
		if (!($dependencies['dataProviderModule'] instanceof DataProviderModule)) {
			throw new InvalidArgumentException('Invalid data provider factory given in dependencies for ViewFactory');
		}
		if (!is_dir($dependencies['viewDirectory'])) {
			throw new InvalidArgumentException('Invalid view directory given in dependencies for ViewFactory');
		}
	}

	private function getViewListManifest($manifestPath)
	{
		if ($this->isCached(['viewListManifest', $manifestPath])) {
			$viewListManifest = $this->getCached(['viewListManifest', $manifestPath]);
		} else {
			$filePath = $this->getFileFromManifestPath($manifestPath);
			$fileContents = file_get_contents($filePath);
			$viewListManifest = json_decode($fileContents, true);

			$this->setCached(['viewListManifest', $manifestPath], $viewListManifest);
		}

		return $viewListManifest;
	}

	private function getManifestFilePath($viewPath)
	{
		$pathExtension = $this->getPathExtension($viewPath);
		$viewPath = preg_replace(sprintf('/%s$/', $pathExtension), '', $viewPath);

		if ($this->isCached(['view', $viewPath, 'manifestPath'])) {
			$manifestPath = $this->getCached(['view', $viewPath, 'manifestPath']);
		} else {
			$manifestPathParts = explode('/', $viewPath);
			$viewPathParts = array();
			$manifestPath = '';

			$found = false;
			$counter = 0;
			$limit = count($manifestPathParts);
			while ($counter <= $limit) {
				$counter++;
				$manifestPath = implode(DIRECTORY_SEPARATOR, $manifestPathParts);
				$manifestFile = $this->getFileFromManifestPath($manifestPath);

				if (is_file($manifestFile)) {
					$found = true;
					break;
				}
				array_unshift($viewPathParts, array_pop($manifestPathParts));
			}

			if (!$found) {
				throw new InvalidRequestException(sprintf('View not found with requested path: `%s`', $viewPath));
			}

			$this->setCached(['view', $viewPath, 'manifestPath'], $manifestPath);
		}

		return $manifestPath;
	}

	private function getPathExtension($path)
	{
		$extensionStartPos = strrpos($path, '.');
		$extension = null;
		if ($extensionStartPos !== false) {
			$extension = substr($path, $extensionStartPos);
		}
		return $extension;
	}

	private function getFileFromManifestPath($manifestPath)
	{
		return $this->viewManifestDirectory . DIRECTORY_SEPARATOR . $manifestPath . '.json';
	}

	private function padViewManifest(array $viewManifest)
	{
		if (!array_key_exists('name', $viewManifest)) {
			$viewManifest['name'] = null;
		}
		if (!array_key_exists('engine', $viewManifest)) {
			$viewManifest['engine'] = null;
		}
		if (!array_key_exists('path', $viewManifest)) {
			$viewManifest['path'] = null;
		}
		if (!array_key_exists('dataProviders', $viewManifest)) {
			$viewManifest['dataProviders'] = array();
		}
		return $viewManifest;
	}

	private function buildDataProviders(array $providerManifests)
	{
		$providers = array();
		foreach ($providerManifests as $providerName => $providerManifest) {
			$providers[] = $this->dataProviderModule->buildProvider($providerManifest)
				->setName($providerName);
		}
		return $providers;
	}
}
