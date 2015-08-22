<?php
namespace Sloth;

class App
{
	/**
	 * @var Base\Config
	 */
	protected $config;

	public function __construct(Base\Config $config)
	{
		$this->config = $config;
	}

    public function module($name)
    {
        $moduleClass = $this->config->modules()->get($name);
        return new $moduleClass($this);
    }

	public function database()
	{
		return $this->config->initialisation()->getDatabase();
	}

    public function render()
    {
        return $this->config->initialisation()->getRenderer();
    }

	public function rootDirectory()
	{
		return $this->config->rootDirectory();
	}

	public function rootNamespace()
	{
		return $this->config->rootNamespace();
	}

	public function rootUrl()
	{
		return $this->config->rootUrl();
	}

    public function createUrl(array $pathParts = array())
    {
        $url = $this->rootUrl();
        if (!empty($pathParts)) {
            $path = implode('/', $pathParts);
            $url = sprintf('%s/%s', $url, $path);
        }
        return rtrim($url, '/');
    }

    public function redirect($newUrl)
    {
        header(sprintf('Location: %s', $newUrl));
        exit;
    }
}
