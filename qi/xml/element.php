<?

class Qi_XML_Element extends SimpleXMLIterator
{
  protected static $NAMESPACES = array();
  public static $ISO = array();

  public function registerXPathNamespace($prefix, $ns)
  {
    self::$NAMESPACES[spl_object_hash($this)][$prefix] = $ns;
    parent::registerXPathNamespace($prefix, $ns);
  }

  public function current()
  {
    $current = parent::current();
    $namespaces = self::$NAMESPACES[spl_object_hash($this)];
    $iso = self::_iso();
    return new Qi_Xml($current, $namespaces, $iso);
  }

  public function __toString()
  {
    $str = parent::__toString();
    return $this->_iso() ? utf8_decode($str) : $str;
  }

  protected function _iso() {
    return @self::$ISO[spl_object_hash($this)];
  }

  public function asXML($filename = null)
  {
    if (!$this->_iso()) return $filename ? parent::asXML($filename) : parent::asXML();
    $xml = @parent::asXML();
    $xml = utf8_decode($xml);
    if ($filename) file_put_contents($filename, $xml);
    return $xml;
  }
  
}

?>