<?

class Qi_Gdata_Site_Entry implements IteratorAggregate, ArrayAccess
{
  public $titulo;
  public $conteudo;
  public $nome;
  public $caminho;
  public $id;
  public $tipo;

  protected $gsite;
  protected $entry;

  public function __construct($gsite, $entry)
  {
    $this->gsite = $gsite;
    $this->entry = $entry;

    $this->titulo = (string)$entry->title;
    // APENAS listitem NÃO POSSUI <link rel="alternate"... nem <title />
    $this->conteudo = (string)$entry->xpath_first("atom:link[@rel = 'alternate']/@href");
    if ($this->conteudo):
      $this->caminho = str_replace($gsite->url, "", $this->conteudo);
      $this->id = strtr($this->caminho, array("/" => ":"));
      $_ = explode("/", $this->caminho); // Only variables should be passed by reference
      $this->nome = array_pop($_); // Only variables should be passed by reference
      $this->caminho_pai = implode("/", $_);
    endif;
  }

  public function __get($name)
  {
    return $this->$name();
  }

  public function __call($name, $args)
  {
    return $this->$name;
  }

  public function itens()
  {
    return array();
  }

  public function items($arg = null)
  {
    return $this->itens($arg);
  }

  public function getIterator()
  {
    return new ArrayIterator($this->itens());
  }

  public function __toString()
  {
    return (string)$this->titulo;
  }

  public function offsetExists($offset)
  {
    return isset($this->$offset);
  }

  public function offsetUnset($offset)
  {
    throw new DomainException("nao eh permitido excluir este valor.");
  }

  public function offsetGet($offset)
  {
    return $this->$offset;
  }

  public function offsetSet($offset, $value)
  {
    throw new DomainException("nao eh permitido alterar este valor.");
  }
}

?>