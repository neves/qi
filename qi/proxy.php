<?

class Qi_Proxy implements ArrayAccess, Countable, IteratorAggregate
{
  protected $_proxy = null;

  public function __construct($proxy)
  {
    $this->_proxy = $proxy;
  }

  // ==================== ArrayAccess ====================

  public function offsetExists($offset)
  {
    return isset($this->_proxy[$offset]);
  }

  public function offsetGet($offset)
  {
    return $this->_proxy[$offset];
  }

  public function offsetSet($offset, $value)
  {
    return $this->_proxy[$offset] = $value;
  }

  public function offsetUnset($offset)
  {
    unset($this->_proxy[$offset]);
  }

  // ==================== Countable ====================

  public function count()
  {
    return count($this->_proxy);
  }

  // ==================== IteratorAggregate ====================

  public function getIterator()
  {
    return $this->_proxy;
  }

  // ==================== __get, __set, __call ====================

  public function __get($name)
  {
    return $this->_proxy->$name;
  }

  public function __set($name, $value)
  {
    return $this->_proxy->$name = $value;
  }

  public function __call($method, $args)
  {
    return call_user_func_array(array($this->_proxy, $method), $args);
  }

  public function __toString()
  {
    return (string)$this->_proxy;
  }
}

?>