<?php
namespace SlothDemo\Resource;

use Sloth\Module\Resource\ResourceFactory;

class UserFactory extends ResourceFactory
{
    public function getBy(array $attributes, array $filters = array())
    {
        echo "CUSTOM RESOURCE FACTORY :: getBy";
        return parent::getBy($attributes, $filters);
    }

    public function search(array $attributesToInclude, array $filters)
    {
        echo "CUSTOM RESOURCE FACTORY :: search";
        return parent::search($attributesToInclude, $filters);
    }

    public function create(array $attributes)
    {
        echo "CUSTOM RESOURCE FACTORY :: create";
        return parent::create($attributes);
    }

    public function update(array $filters, array $attributes)
    {
        echo "CUSTOM RESOURCE FACTORY :: update";
        return parent::update($filters, $attributes);
    }

    public function delete(array $filters)
    {
        echo "CUSTOM RESOURCE FACTORY :: delete";
        return parent::delete($filters);
    }
}
