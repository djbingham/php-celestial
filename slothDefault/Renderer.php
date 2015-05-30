<?php
namespace SlothDefault;

use Sloth\Exception;

class Renderer
{
    private $app;
    private $viewDir;
    private $templateEngine;

	public function __construct(array $options)
	{
        $this->app = $options['app'];
        $this->viewDir = $options['viewDirectory'];
        if (array_key_exists('templateEngine', $options)) {
            $this->templateEngine = $options['templateEngine'];
        }
	}

	public function full($view = null, array $parameters = array())
	{
        if (!array_key_exists('app', $parameters)) {
            $parameters['app'] = $this->app;
        }
		extract($parameters);
		$content = $this->capturePartial($view, $parameters);
		if (!empty($this->template)) {
			echo $this->template;
		} else {
			echo $content;
		}
	}

	public function partial($view = null, array $parameters = array())
	{
        if (!array_key_exists('app', $parameters)) {
            $parameters['app'] = $this->app;
        }
		$viewFile = sprintf('%s%s%s.php', $this->viewDir, DIRECTORY_SEPARATOR, $view);
		if (!file_exists($viewFile)) {
			throw new Exception\NotFoundException(
				sprintf('Failed to find view file: %s', $viewFile)
			);
		}
		extract($parameters);
		include $viewFile;
	}

	public function captureFull($view = null, array $parameters = array())
	{
		ob_start();
		$this->full($view, $parameters);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public function capturePartial($view = null, array $parameters = array())
	{
		ob_start();
		$this->partial($view, $parameters);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}
