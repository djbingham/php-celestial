<?php
namespace SlothDemo\Resource;

use Sloth\Module\Resource\Base;
use Sloth\Module\Resource\ResourceFactory;

class DerivationFactory extends ResourceFactory
{
    public function getBy(array $attributes)
    {
        echo "CUSTOM RESOURCE FACTORY :: getBy";
        return parent::getBy($attributes);
    }

    public function search(array $filters)
    {
        echo "CUSTOM RESOURCE FACTORY :: search";
        return parent::search($filters);
    }

    public function create(array $attributes)
    {
        echo "CUSTOM RESOURCE FACTORY :: create";
        return parent::create($attributes);
    }

    public function update(Base\Resource $resource)
    {
        echo "CUSTOM RESOURCE FACTORY :: update";
        return parent::update($resource);
    }

    public function delete(Base\Resource $resource)
    {
        echo "CUSTOM RESOURCE FACTORY :: delete";
        return parent::delete($resource);
    }
}
