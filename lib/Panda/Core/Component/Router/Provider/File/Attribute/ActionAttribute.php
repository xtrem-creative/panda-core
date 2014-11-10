<?php

namespace Panda\Core\Component\Router\Provider\File\Attribute;


class ActionAttribute implements Attribute
{
    private $name;

    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        if (is_string($name) && !empty($name)) {
            $this->name = $name;
        } else {
            throw new \InvalidArgumentException('Invalid action "'.$name.'"');
        }
    }


} 