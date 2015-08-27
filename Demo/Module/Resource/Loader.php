<?php
namespace SlothDemo\Module\Resource;

use Sloth\App;
use Sloth\Module\Resource\Renderer;

class Loader
{
    /**
     * @var App
     */
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function parser()
    {
        return new RequestParser($this->app);
    }

    public function renderer()
    {
        return new Renderer($this->app);
    }
}
