<?

require_once __DIR__."/entry.php";

class Qi_Gdata_Site_WebPage extends Qi_Gdata_Site_Entry
{
  protected $gsite;

  public function __construct($gsite, $entry)
  {
    parent::__construct($gsite, $entry);
    $content = $entry->content->xpath("descendant::xhtml:table/descendant::xhtml:div[@dir = 'ltr']");
    $this->conteudo = (string)$entry->content->div->children()->asXML();
    $this->remover_links_das_imagens_do_conteudo();
  }

  protected function remover_links_das_imagens_do_conteudo()
  {
    $pattern = 'href="http://sites.google.com/a/.+?/.+?/.+?\?attredirects=0"';
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
    return $this->gsite->paginas($path);
  }

  public function itens()
  {
    return $this->sub_paginas();
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

  public function pastas()
  {
    $pastas = array();
    foreach($this->arquivos() as $arquivo):
      $pasta = $arquivo->pasta;
      if ( ! isset($pastas[$pasta]) ) $pastas[$pasta] = array();
      $pastas[$pasta][$arquivo->nome] = $arquivo;
    endforeach;
    return $pastas;
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
    return "<a href=\"$url\">$texto</a>";
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

  public function link_img($mini_width = null, $mini_height = null, $zoom_width = null, $zoom_height = null)
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