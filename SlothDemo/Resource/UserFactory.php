<?php
namespace SlothDemo\Resource;

use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\ResourceFactory;

class UserFactory extends ResourceFactory
{
    public function getBy(AttributeList $attributes, array $filters = array())
    {
        echo "<p>CUSTOM RESOURCE FACTORY :: getBy</p>";
        return parent::getBy($attributes, $filters);
    }

    public function search(AttributeList $attributesToInclude, array $filters)
    {
        echo "<p>CUSTOM RESOURCE FACTORY :: search</p>";
        return parent::search($attributesToInclude, $filters);
    }

    public function create(array $attributes)
    {
        echo "<p>CUSTOM RESOURCE FACTORY :: create</p>";
        return parent::create($attributes);
    }

    public function update(array $filters, array $attributes)
    {
        echo "<p>CUSTOM RESOURCE FACTORY :: update</p>";
        return parent::update($filters, $attributes);
    }

    public function delete(array $filters)
    {
        echo "<p>CUSTOM RESOURCE FACTORY :: delete</p>";
        return parent::delete($filters);
    }
}
