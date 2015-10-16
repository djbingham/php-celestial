<?php
namespace Sloth\Module\Render;

use Helper\InternalCacheTrait;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Render\Face\ViewFactoryInterface;

class ViewFactory implements ViewFactoryInterface
{
	use InternalCacheTrait;

	/**
	 * @var RenderEngineFactory
	 */
	private $renderEngineFactory;

	/**
	 * @var DataProviderFactory
	 */
	private $dataProviderFactory;

	/**
	 * @var string
	 */
	private $viewDirectory;

	public function __construct(array $dependencies)
	{
		$this->validateDependencies($dependencies);
		$this->renderEngineFactory = $dependencies['renderEngineFactory'];
		$this->dataProviderFactory = $dependencies['dataProviderFactory'];
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
			$manifestPath = $this->locateViewManifestFile($viewPath);
			$viewListManifest = $this->getViewListManifest($manifestPath);
			$viewName = ltrim('/', preg_replace(sprintf('/^%s/', $manifestPath), '', $viewPath));

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
		$viewManifest = $this->padViewManifest($viewManifest);

		$view = new \Sloth\Module\Render\View();
		$view->name = $viewName;
		$view->path = $this->viewDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $viewManifest['path']);
		$view->engine = $this->renderEngineFactory->getByName($viewManifest['engine']);
		$view->dataProviders = $this->dataProviderFactory->buildProviders($viewManifest['dataProviders']);

		return $view;
	}

	public function build(array $viewManifest)
	{
		$viewManifest = $this->padViewManifest($viewManifest);

		$view = new \Sloth\Module\Render\View();
		$view->name = $viewManifest['name'];
		$view->path = $this->viewDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $viewManifest['path']);
		$view->engine = $this->renderEngineFactory->getByName($viewManifest['engine']);
		$view->dataProviders = $this->dataProviderFactory->buildProviders($viewManifest['dataProviders']);

		return $view;
	}

	private function validateDependencies(array $dependencies)
	{
		$required = array('renderEngineFactory', 'dataProviderFactory', 'viewDirectory');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for ViewFactory in Render module: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['renderEngineFactory'] instanceof RenderEngineFactory)) {
			throw new InvalidArgumentException('Invalid render engine factory given in dependencies for ViewFactory');
		}
		if (!($dependencies['dataProviderFactory'] instanceof DataProviderFactory)) {
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
			$fileContents = file_get_contents($manifestPath);
			$viewListManifest = json_decode($fileContents);

			$this->setCached(['viewListManifest', $manifestPath], $viewListManifest);
		}

		return $viewListManifest;
	}

	private function locateViewManifestFile($viewPath)
	{
		if ($this->isCached(['view', $viewPath, 'manifestFile'])) {
			$manifestPath = $this->getCached(['view', $viewPath, 'manifestFile']);
		} else {
			$viewPathParts = explode('/', $viewPath);
			$manifestPath = $this->viewDirectory;

			$found = false;
			while (!empty($viewPathParts)) {
				$manifestPath .= DIRECTORY_SEPARATOR . array_shift($viewPathParts);
				if (is_file($manifestPath)) {
					$found = true;
					break;
				}
			}

			if (!$found) {
				throw new InvalidRequestException(sprintf('View not found with requested path: `%s`', $viewPath));
			}

			$this->setCached(['view', $viewPath, 'manifestFile'], $manifestPath);
		}

		return $manifestPath;
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
}
