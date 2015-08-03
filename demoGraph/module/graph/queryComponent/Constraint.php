<?php
namespace DemoGraph\Module\Graph\QueryComponent;

class Constraint
{
    /**
     * @var mixed
     */
    private $subject;

    /**
     * @var mixed
     */
    private $value;

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }


}
