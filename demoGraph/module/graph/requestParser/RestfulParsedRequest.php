<?php
namespace DemoGraph\Module\Graph\RequestParser;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Request;

class RestfulParsedRequest extends Request implements ParsedRequestInterface
{
    /**
     * @var string
     */
    protected $manifest;

    /**
     * @var string
     */
    protected $factoryClass;

    /**
     * @var string
     */
    protected $resourceRoute;

    /**
     * @var string
     */
    protected $unresolvedRoute;

    /**
     * @var string
     */
    protected $format;

    public function __construct(array $properties)
    {
        foreach ($properties as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(
                    sprintf('Unrecognised property given to RestfulParsedRequest: %s', $key)
                );
            }
            $this->$key = $value;
        }
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public function getFactoryClass()
    {
        return $this->factoryClass;
    }

    public function getResourceRoute()
    {
        return $this->resourceRoute;
    }

    public function getUnresolvedRoute()
    {
        return $this->unresolvedRoute;
    }

    public function getFormat()
    {
        return $this->format;
    }
}
