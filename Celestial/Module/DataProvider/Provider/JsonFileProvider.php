<?php
namespace Celestial\Module\DataProvider\Provider;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\DataProvider\Base\AbstractDataProvider;

class JsonFileProvider extends AbstractDataProvider
{
	public function getData(array $options)
	{
		$this->validateOptions($options);

		$filePath = $this->app->rootDirectory() . DIRECTORY_SEPARATOR . $options['filePath'];

		return json_decode(file_get_contents($filePath), true);
	}

	protected function validateDependencies(array $dependencies)
	{

	}

	protected function validateOptions(array $options)
	{
		if (!array_key_exists('filePath', $options)) {
			throw new InvalidArgumentException('Missing `filePath` in options for data provider `JsonFileProvider`');
		}
	}
}
