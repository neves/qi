<?

require_once __DIR__."/../xml.php";
require_once __DIR__."/site/entry.php";
require_once __DIR__."/site/webpage.php";

/**
 * @TODO: criar uma maneira para ordenar as páginas, pois o gsite não suporta ordenação.
 */
class Qi_Gdata_Site
{
  const FEED = "http://sites.google.com/feeds/content/%s/%s/";
  const URL = "http://sites.google.com/a/%s/%s/";

  public static $_singleton = null;
  public static $f5 = true; // SHIFT+F5 no firefox e chrome. CTRL+F5 no IE.
  public static $utf8 = false;

  public $feed = "";
  public $url = "";
  public $iso88591 = true;

  protected $sxml = null;
  protected $_cfg = null;

  public function __construct($site = "adm", $domain = "qi64.com", $iso88591 = null)
  {
    $iso88591 = $iso88591 === null ? !self::$utf8 : $iso88591;
    $this->iso88591 = $iso88591;
    $this->feed = sprintf(self::FEED, $domain, $site);
    $this->url = sprintf(self::URL, $domain, $site);
  }

  // STATIC METHODS

  public static function carregar($site = "adm", $domain = "qi64.com", $file = null, $iso88591 = null)
  {
    if (!$file) $file = "$site.$domain.xml";
    $class = get_called_class();
    self::$_singleton = new $class($site, $domain, $iso88591);
    if ( ! file_exists($file) || self::is_refresh() ):// force refresh
      self::singleton()->download($file);
    endif;
    self::singleton()->load_from_url($file);
    return self::singleton();
  }

  protected static function is_refresh()
  {
    $ie = strpos(@$_SERVER["HTTP_USER_AGENT"], "MSIE") !== false;
    $force_refresh = $ie ? isset($_SERVER["HTTP_CACHE_CONTROL"]) : isset($_SERVER["HTTP_PRAGMA"]);
    if ( isset($_REQUEST["gsite-update"]) ) $force_refresh = true;
    return self::$f5 && $force_refresh;
  }

  public static function static_pagina($caminho = null)
  {
    return self::singleton()->pagina($caminho);
  }

  public static function singleton()
  {
    if (!self::$_singleton)
      throw new BadMethodCallException("execute Qi\\Gdata\\Site::carregar('site', 'dominio') para inicializar o objeto");
    return self::$_singleton;
  }

  public static function static_cfg($codigo = null)
  {
    return self::singleton()->cfg($codigo);
  }

  // INSTANCE METHODS

  public function download($file)
  {
    $dom = new DOMDocument("1.0");
    $dom->preserveWhiteSpace = false;
    $url = $this->feed."?max-results=9876&strict=true";
    $dom->load($url);
    $dom->formatOutput = true;
    if ($this->iso88591) $dom->encoding = "iso-8859-1";
    $dom->save($file);
  }

  public function load_from_url($url = null)
  {
    if ($url == null) $url = $this->feed."?max-results=9876&strict=true";
    $this->sxml = Qi_Xml::load_file($url);
    $this->sxml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
    $this->sxml->registerXPathNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
  }

  public function page_by_path($path)
  {
    $url = $this->url.$path;
    $entry = $this->sxml->xpath_first("atom:entry[atom:link/@rel = 'alternate' and atom:link/@href = '$url']");
    if (!$entry) throw new DomainException("pagina [$path] nao existe.");
    return $entry;
  }

  public function pagina($caminho = null)
  {
    if (!$caminho) return $this->paginas();
    $entry = $this->page_by_path($caminho);
    return $this->factory_pagina($entry);
  }

  public function paginas($xpath = null)
  {
    if (!$xpath) $xpath = "atom:entry[not(atom:link/@rel = 'http://schemas.google.com/sites/2008#parent') and sites:pageName != 'cfg']";
    $entrys = $this->sxml->xpath($xpath);
    return $this->_wrap($entrys);
  }

  public function cfg($codigo)
  {
    if (!$this->_cfg)
      $this->_cfg = (string)$this->page_by_path("cfg")->id;

    $url = $this->url."cfg/".$codigo;
    $is_parent_from_cfg = "atom:link[@rel = 'http://schemas.google.com/sites/2008#parent' and @href = '$this->_cfg']";

    $xpath = "atom:entry[$is_parent_from_cfg and atom:link[@rel = 'alternate' and @href = '$url']]/atom:link[@rel = 'alternate']/@href";
    $cfg = (string)$this->sxml->xpath_first($xpath);
    if ($cfg != "") return $cfg;

    $xpath = "atom:entry[$is_parent_from_cfg and gs:field[@name = 'codigo' and text() = '$codigo']]/gs:field[@name = 'valor']/text()";
    return (string)$this->sxml->xpath_first($xpath);
  }

  public function _wrap($entrys)
  {
    $pages = array();
    foreach($entrys as $entry):
      $pagina = $this->factory_pagina($entry);
      $pages[] = $pagina;
    endforeach;
    return $pages;
  }

  protected function factory_pagina($entry)
  {
    $tipo = (string)$entry->xpath_first("atom:category[@scheme = 'http://schemas.google.com/g/2005#kind']/@label");
    $class = "Qi_Gdata_Site_$tipo";
    $pagina = new $class($this, $entry);
    // TODOS TEM <category webpage, listpage:listitem, filecabinet:attachment, announcementspage:announcement
    $pagina->tipo = $tipo;
    return $pagina;
  }
}

// HELPER FUNCTIONS

function gsite_pagina($caminho = null)
{
  return gsite($caminho);
}

function gsite($caminho = null)
{
  return Qi_Gdata_Site::static_pagina($caminho);
}

function gsite_cfg($codigo)
{
  return Qi_Gdata_Site::static_cfg($codigo);
}

?>
