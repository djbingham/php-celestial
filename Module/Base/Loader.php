<?php
namespace Sloth\Module\Base;

use Sloth\App;
use Sloth\Request;

interface Loader
{
    public function __construct(App $app);
    public function parser();
    public function renderer();
}