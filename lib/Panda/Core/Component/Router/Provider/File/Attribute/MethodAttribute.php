<?php

namespace Panda\Core\Component\Router\Provider\File\Attribute;


class MethodAttribute implements Attribute
{
    private $value;

    public function __construct($value)
    {
        $this->setValue($value);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function setValue(array $value)
    {
        //TODO! check HTTP method
        $this->value = $value;
    }


} 