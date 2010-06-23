<?

require_once __DIR__."/proxy.php";
require_once __DIR__."/xml/element.php";

class Qi_Xml extends Qi_Proxy
{
  protected $_namespaces = array();
  protected $_iso = true;

  public function __construct($proxy, $namespaces = array(), $iso = true)
  {
    $this->_namespaces = $namespaces;
    $this->_proxy = $proxy;
    $this->_iso = $iso;
    Qi_XML_Element::$ISO[spl_object_hash($proxy)] = $iso;
    $this->_register_namespaces();
  }

  public function registerXPathNamespace($prefix, $ns)
  {
    $this->_namespaces[$prefix] = $ns;
    return $this->_proxy->registerXPathNamespace($prefix, $ns);
  }

  public function xpath($path)
  {
    $result = $this->_proxy->xpath($path);
    if ($result === false) return $result;
    foreach($result as $k => $v) $result[$k] = $this->_wrap($v);
    return $result;
  }

  public function xpath_str($path)
  {
    $result = $this->_proxy->xpath($path);
    if ($result === false) return $result;
    $result = array_map($this->_iso ? "utf8_decode" : "strval", $result);
    return $result;
  }

  public function xpath_first($path)
  {
    $result = $this->xpath($path);
    return is_array($result) ? reset($result) : $result;
  }

  public function __get($name)
  {
    $child = $this->_proxy->$name;
    if ($child === null) return $child;
    return $this->_wrap($child);
  }

  public function offsetGet($offset)
  {
    $child = $this->_proxy[$offset];
    if ($child === null) return $child;
    return $this->_wrap($child);
  }

  public function __toString()
  {
    $str = parent::__toString();
    return $this->_iso ? utf8_decode($str) : $str;
  }

  public function asXML($filename = null)
  {
    if (!$this->_iso) return parent::asXML($filename);
    $xml = parent::asXML();
    $xml = utf8_decode($xml);
    if ($filename) file_put_contents($filename, $xml);
    return $xml;
  }

  // ==================== STATIC METHODS ====================

  public static function load_file($file, $iso = true, $element_class = "Qi_XML_Element")
  {
    return self::_static_wrap(simplexml_load_file($file, $element_class), array(), $iso);
  }

  public static function load_str($str, $iso = true, $element_class = "Qi_XML_Element")
  {
    return self::_static_wrap(simplexml_load_string($str, $element_class), $iso);
  }

  /**
   * wrap o SimpleXMLElement em um Qi_Xml e repassa os namespaces definidos
   */
  protected static function _static_wrap($sxml, $namespaces = array(), $iso = true)
  {
    return new Qi_Xml($sxml, $namespaces, $iso);
  }

  // ==================== PROTECTED METHODS ====================

  protected function _wrap($sxml)
  {
    return self::_static_wrap($sxml, $this->_namespaces, $this->_iso);
  }

  protected function _register_namespaces()
  {
    foreach($this->_namespaces as $prefix => $ns)
      $this->_proxy->registerXPathNamespace($prefix, $ns);
  }
}

?>