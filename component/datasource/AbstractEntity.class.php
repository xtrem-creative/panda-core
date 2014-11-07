<?php

/**
 * Panda entity
 * 
 * A class to design entities (object representation of datasources results)
 * 
 * @package Panda
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

namespace Panda\component\datasource;

abstract class AbstractEntity implements \ArrayAccess, \Iterator
{

	private $_keys;
	private $_index;

	public function hydrate(array $array)
	{
		foreach ($array as $key => $value) {
			if (method_exists($this, 'set' . ucfirst($key))) {
				$this->{'set' . ucfirst($key)}($value);
			} else {
				throw new \InvalidArgumentException('Unknown entity attribute "' . $key . '"');
			}
		}
	}

	public function rewind()
    {
    	$this->_keys = array_filter(array_keys(get_object_vars($this)), function($key){return $key[0] !== '_';});
		$this->_index = 0;
    }
  
    public function current()
    {
    	return $this->{$this->_keys[$this->_index]};
    }
  
    public function key() 
    {
    	return $this->_keys[$this->_index];
    }
  
    public function next() 
    {
    	++$this->_index;
    }

    public function valid()
    {
    	return isset($this->_keys[$this->_index]);
    }

	public function offsetExists($key)
	{
		return property_exists($this, $key);
	}

	public function offsetGet($key)
	{
		if ($this->offsetExists($key)) {
			return $this->{'get' . ucfirst($key)}();
		}
	}

	public function offsetSet($key, $value)
	{
		if ($this->offsetExists($key)) {
			return $this->{'set' . ucfirst($key)}($value);
		}
	}

	public function offsetUnset($key)
	{
		throw new \RuntimeException('Unable to unset an entity value.');
	}
}