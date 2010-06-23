<?

require_once __DIR__."/entry.php";

class Qi_Gdata_Site_WebPage extends Qi_Gdata_Site_Entry
{
  protected $gsite;

  public function __construct($gsite, $entry)
  {
    parent::__construct($gsite, $entry);
    $content = $entry->content->xpath("descendant::xhtml:table/descendant::xhtml:div[@dir = 'ltr']");
    $dom = new DomDocument();
    $dom->loadHTML((string)$entry->content->div->children()->asXML());
    $this->conteudo = $dom->saveHTML();
    //$this->conteudo = (string)$entry->content->div->children()->asXML();
    $this->_remover_links_das_imagens_do_conteudo();
    $this->_remover_links_tocs();
  }

  protected function _remover_links_das_imagens_do_conteudo()
  {
    $pattern = 'href="http://sites.google.com/a/.+?/.+?/.+?\?attredirects=0"';
    $this->conteudo = preg_replace("!$pattern!", "", $this->conteudo);
  }
  
  protected function _remover_links_tocs()
  {
    $pattern = '<a name="TOC-.+?"/>';
    $this->conteudo = preg_replace("!$pattern!", "", $this->conteudo);
  }

  public function sub_paginas()
  {
    $parent = (string)$this->entry->id;
    $is_parent = "@rel = 'http://schemas.google.com/sites/2008#parent'";
    $not_listitem = "atom:category/@label != 'listitem'";
    $not_attachment = "atom:category/@label != 'attachment'";
    $not_announcement = "atom:category/@label != 'announcement'";
    $not = "$not_listitem and $not_attachment and $not_announcement";
    $path = "atom:entry[ atom:link[$is_parent and @href = '$parent'] and $not]";
    return $this->gsite->paginas($path);
  }

  public function arquivos()
  {
    $parent = (string)$this->entry->id;
    $is_parent = "@rel = 'http://schemas.google.com/sites/2008#parent'";
    $is_attachment = "atom:category/@label = 'attachment'";
    $path = "atom:entry[ atom:link[$is_parent and @href = '$parent'] and $is_attachment]";
    $arquivos = $this->gsite->paginas($path);
    $arquivos_filtrados = array();
    $imagens = $this->imagens();
    foreach($arquivos as $arquivo):
        if ( isset($imagens[$arquivo->conteudo]) ) continue;
        $arquivos_filtrados[] = $arquivo;
    endforeach;
    return $arquivos_filtrados;
  }

  /**
   * Apenas um apelido para arquivos
   */
  public function anexos()
  {
    return $this->arquivos;
  }

  public function itens()
  {
    return $this->sub_paginas();
  }

  public function imagens()
  {
    $links = $this->entry->xpath("descendant::xhtml:a[starts-with(@href, 'http://sites.google.com/a/') and @imageanchor='1']");
    $imagens = array();
    foreach($links as $link):
      $href = str_replace("?attredirects=0", "", $link["href"]);
      $src = (string)$link->xpath_first("xhtml:img/@src");
      $imagens[$href] = $src;
    endforeach;
    return $imagens;
  }

  public function videos()
  {
    $videos = $this->entry->xpath("descendant::xhtml:div[contains(@class, 'sites-embed-type-youtube')]");
    $ret = array();
    foreach($videos as $video):
      $ret[] = new Qi_Gdata_Site_Video(
        (string)$video->xpath_first("descendant::xhtml:embed/@src"),
        (string)$video->xpath_first("../xhtml:h4")
      );
    endforeach;
    return $ret;
  }
}


class Qi_Gdata_Site_Video
{
  const TB = "http://img.youtube.com/vi/%s/1.jpg";
  const PREVIEW = "http://img.youtube.com/vi/%s/0.jpg";
  const VIDEO = "http://www.youtube.com/v/%s";

  public $codigo;
  public $titulo;

  public function __construct($url, $titulo = null)
  {
    $this->titulo = $titulo;
    $data = parse_url($url);
    list($foo, $bar, $this->codigo) = explode("/", $data["path"]);
  }

  public function __get($name)
  {
    return $this->$name();
  }

  public function tb_url()
  {
    return sprintf(self::TB, $this->codigo);
  }

  public function preview_url()
  {
    return sprintf(self::PREVIEW, $this->codigo);
  }

  public function video_url()
  {
    return sprintf(self::VIDEO, $this->codigo);
  }

  public function tb_img()
  {
    return <<<EOF
<img src="$this->tb_url" width="120" height="90" />
EOF;
  }

  public function preview_img()
  {
    return <<<EOF
<img src="$this->preview_url" width="480" height="360" />
EOF;
  }

  public function html($width = "425", $height = "355")
  {
    return <<<EOF
<object width="$width" height="$height">
  <param value="$this->video_url" name="movie">
  <param value="transparent" name="wmode">
  <embed width="$width" height="$height" wmode="transparent" type="application/x-shockwave-flash" src="$this->video_url">
</object>
EOF;
  }
}


class Qi_Gdata_Site_FileCabinet extends Qi_Gdata_Site_WebPage
{
  public function __construct($gsite, $entry)
  {
    parent::__construct($gsite, $entry);
  }

  public function itens()
  {
    return $this->arquivos();
  }

  public function pastas($index = null)
  {
    $pastas = array();
    foreach($this->arquivos() as $arquivo):
      $pasta = $arquivo->pasta;
      if ( ! isset($pastas[$pasta]) ) $pastas[$pasta] = array();
      $pastas[$pasta][$arquivo->nome] = $arquivo;
    endforeach;
    return $index === null ? $pastas : (@$pastas[$index] ?: array());
  }
}

class Qi_Gdata_Site_Attachment extends Qi_Gdata_Site_Entry
{
  public $pasta = "";

  public function __construct($gsite, $entry)
  {
    parent::__construct($gsite, $entry);
    $this->pasta = (string)$entry->xpath_first("atom:category[@scheme = 'http://schemas.google.com/sites/2008#folder']/@term");
    $this->descricao = (string)$entry->summary;
    if ($this->descricao)
      $this->titulo = $this->descricao;
  }

  public function link($texto = null, $width = null, $height = null)
  {
    if ($texto === null) $texto = h($this->titulo);
    $url = $this->url($width, $height);
    $rel = $this->pasta ?: $this->caminho_pai;
    return <<<a

<a rel="$rel" href="$url">
  $texto
</a>

a;
  }

  public function url($width = null, $height = null)
  {
    $args = "";
    if ($width ) $args  = "width=$width";
    if ($args  ) $args .= "&";
    if ($height) $args .= "height=$height";
    if ($args ) $args = "?$args";
    return $this->conteudo."$args";
  }

  public function link_img($mini_width = 104, $mini_height = 104, $zoom_width = 800, $zoom_height = 800)
  {
    $img = $this->img($mini_width, $mini_height);
    return $this->link($img, $zoom_width, $zoom_height);
  }

  public function img($width = null, $height = null)
  {
    $url = $this->url($width, $height);
    return "<img src=\"$url\" />";
  }
}

class Qi_Gdata_Site_ListPage extends Qi_Gdata_Site_WebPage
{
  public $campos = array();

  public function __construct($gsite, $entry)
  {
    parent::__construct($gsite, $entry);
    $this->campos = $entry->xpath_str("gs:data/gs:column/@name");
  }

  public function itens()
  {
    $parent = (string)$this->entry->id;
    $is_parent = "@rel = 'http://schemas.google.com/sites/2008#parent'";
    $is_listitem = "atom:category/@label = 'listitem'";
    $path = "atom:entry[ atom:link[$is_parent and @href = '$parent'] and $is_listitem]";
    return $this->gsite->paginas($path);
  }
}

class Qi_Gdata_Site_ListItem extends Qi_Gdata_Site_Entry
{
  public function __construct($gsite, $entry)
  {
    parent::__construct($gsite, $entry);
    $this->campos = $entry->xpath_str("gs:field/@name");
    $this->valores = $entry->xpath_str("gs:field");
    $this->valores = array_combine($this->campos, $this->valores);
    $this->titulo = reset($this->valores);
    $this->conteudo = next($this->valores);
    foreach($this->valores as $k => $v) $this->$k = $v;
  }

  public function itens()
  {
    return $this->valores;
  }

  public function data($campo, $formato = null)
  {
    $valor = $this[$campo];
    try {
      $d = new DateTime($valor);
      return $formato ? $d->format($formato) : $d;
    }catch(Exception $e) {
      return $formato ? $valor : new DateTime(0);
    }
  }
}

class Qi_Gdata_Site_AnnouncementsPage extends Qi_Gdata_Site_WebPage
{
  public function itens()
  {
    $parent = (string)$this->entry->id;
    $is_parent = "@rel = 'http://schemas.google.com/sites/2008#parent'";
    $is_announcement = "atom:category/@label = 'announcement'";
    $path = "atom:entry[ atom:link[$is_parent and @href = '$parent'] and $is_announcement]";
    return $this->gsite->paginas($path);
  }
}

class Qi_Gdata_Site_Announcement extends Qi_Gdata_Site_WebPage
{
  public function __construct($gsite, $entry)
  {
    parent::__construct($gsite, $entry);
  }
}

?>