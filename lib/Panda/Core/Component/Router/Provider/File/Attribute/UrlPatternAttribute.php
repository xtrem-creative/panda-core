<?php

namespace Panda\Core\Component\Router\Provider\File\Attribute;


class UrlPatternAttribute implements Attribute
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
     * @param $value
     * @throws \InvalidArgumentException
     */
    public function setValue($value)
    {
        if (is_string($value) && !empty($value) && $value[0] === '/') {
            $this->value = $value;
        } else {
            throw new \InvalidArgumentException('Invalid url pattern "'.$value.'"');
        }
    }


} 