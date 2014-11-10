<?php

namespace Panda\Core\Component\Router\Provider\File\Attribute;


class VarsAttribute implements Attribute
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
        $this->value = $value;
    }


} 