<?
/**
 * @DEPENDENCIAS Qi_Util::bool
 */
class Qi_Html
{
	/**
	* usado internamente para manter o estado do flip
	* null significa sem estados e array a lista de estados possíveis
	*/
	private static $flip = null;

	/**
	* Usado principalmente no html, para alterar entre 2 css diferentes:
	* <tr <?= Qi_Html::flip("", 'class="cor-fundo"') ?>>... ou:
	* <tr class="<?= Qi_Html::flip("par", "impar") ?>">...
	* Chamar flip() sem parametros para reiniciar
	* Opções pode ser um array com as opções
	*/
	public static function flip($opcoes = null)
	{
		if ($opcoes === null) return self::$flip = null;
		if (func_num_args() > 1) $opcoes = func_get_args();
		if (self::$flip === null):
			self::$flip = $opcoes;
			return reset(self::$flip);
		else:
			if ($opcoes !== self::$flip):
				self::flip();
				return self::flip($opcoes);
			endif;
			$return = next(self::$flip);
			return $return === false ? reset(self::$flip) : $return;
		endif;
	}

	public static function impar_par()
	{
		return self::flip("impar", "par");
	}

	public static function par_impar()
	{
		return self::flip("par", "impar");
	}

	/**
	 * Retorna uma string no formato 080129_223033 a data 29/01/2008 22:30:33
	 * para ser usada anexada ao nome de um arquivo para burlar o cache
	 * caso não consiga pegar a data do arquivo, retorna um mt_rand()
	 * @param $timestamp_ou_arquivo int|string timestamp ou o nome do arquivo
	**/
	public static function alterado_em($timestamp_ou_arquivo)
	{
		if (is_numeric($timestamp_ou_arquivo)):
			$alterado_em = $timestamp_ou_arquivo;

		elseif (file_exists($timestamp_ou_arquivo)):
				$alterado_em = filemtime($timestamp_ou_arquivo);

		elseif ($timestamp_ou_arquivo[0] == "/"):
			$raiz = $_SERVER["DOCUMENT_ROOT"];
			$timestamp_ou_arquivo = "$raiz$timestamp_ou_arquivo";
			if (file_exists($timestamp_ou_arquivo))
				$alterado_em = filemtime($timestamp_ou_arquivo);
			else
				$alterado_em = mt_rand(); // caso o arquivo não exista, usar um número aleatório

		else:
			$raiz = dirname($_SERVER["SCRIPT_FILENAME"]);
			$timestamp_ou_arquivo = "$raiz/$timestamp_ou_arquivo";
			if (file_exists($timestamp_ou_arquivo))
				$alterado_em = filemtime($timestamp_ou_arquivo);
			else
				$alterado_em = mt_rand(); // caso o arquivo não exista, usar um número aleatório
		endif;

		return date("ymd_His", $alterado_em);
	}

	/**
	* Retorna a meta-tag <base href="http://localhost/pedidos/empresa/" />
	* para a url: http://localhost/pedidos/empresa/index.php
	* @TODO Qual usar por padrão? PHP_SELF ou REQUEST_URI quando mod_rewrite?
	* PHP_SELF estraga formulários mas arruma imagens relativas
	* REQUEST_URI faz o contrário
	*/
	public static function base($retornar_apenas_url = false)
	{
		$protocolo = $_SERVER["SERVER_PROTOCOL"]; // exemplo: HTTP/1.1
		list($protocolo) = explode("/", $protocolo);
		$protocolo = strtolower($protocolo);

		$host = strtolower($_SERVER["HTTP_HOST"]); // exemplo: localhost
		//$url = $_SERVER["PHP_SELF"]; // exemplo: /pedidos/empresa/index.php
		$url = $_SERVER["REQUEST_URI"]; // possui o caminho virtual quando usado mod_rewrite
		$path = pathinfo($url); // pedidos/empresa
		$url = $path["dirname"];
		if( $path["basename"] == $path["filename"] ) // /pedidos/empresa/ (empresa == empresa)
			$url .= "/$path[basename]";

		$url_absoluta = "$protocolo://$host$url/"; // precisa da barra no final
		if ($retornar_apenas_url) return $url_absoluta;
		return <<<BASE
	<base href="$url_absoluta" />

BASE;
	}

	/**
		@param $encoding string iso-8859-1 ou UTF-8
	**/
	public static function xml_prolog($encoding = "iso-8859-1")
	{
		return <<<XML
<?xml version="1.0" encoding="$encoding"?>

XML;
	}

	public static function doctype()
	{
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
	}

	/**
		@param $charset string iso-8859-1 ou UTF-8
	**/
	public static function meta_content_type($charset = "iso-8859-1")
	{
		return <<<ISO
<meta http-equiv="Content-Type" content="text/html; charset=$charset" />

ISO;
	}

	/**
	* Retorna o código necessário para inserir o google analytics na página.
	* Já utiliza o código assincrono lancado no fim de 2009
	*/
	public static function google_analytics($codigo)
    {
		return <<<GOOGLE

<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '$codigo']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>

GOOGLE;
	}

	/**
		Faz um texto ser seguro para inserir no html
	*/
	public static function h($valor)
	{
		if (get_magic_quotes_gpc()) $valor = stripslashes($valor);
		return htmlspecialchars($valor);
	}

	/**
		retorna ' checked="checked"' caso verdadeiro (repare no espaço no começo!)
		$valor pode ser um boolean direto
		caso $comparacao seja informado, $valor será comparado com ele
		se $valor não for booleano, compara com strings padrões que são consideradas true
	*/
	public static function checked($valor, $comparacao = null)
	{
		return Qi_Util::bool($valor, $comparacao) ? ' checked="checked"' : "";
	}

	/**
		retorna ' selected="selected"' caso verdadeiro (repare no espaço no começo!)
		$valor pode ser um boolean direto
		caso $comparacao seja informado, $valor será comparado com ele
		se $valor não for booleano, compara com strings padrões que são consideradas true
	*/
	public static function selected($valor, $comparacao = null)
	{
		return Qi_Util::bool($valor, $comparacao) ? ' selected="selected"' : "";
	}

  /**
  * Renderiza um arquivo php template para uma string
  * os nomes das variáveis são estranhas para evitar conflito com variáveis em $_QI_VARS
  */
  public static function tpl($_QI_ARQUIVO, $_QI_VARS = array(), $_QI_COMENTARIO = true)
  {
    if (!file_exists($_QI_ARQUIVO)) return "";
    if (!is_array($_QI_VARS)) $_QI_VARS = array();
    $_QI_INICIO = microtime(true);
		ob_start();
		try {
			extract($_QI_VARS);
			include $_QI_ARQUIVO;
			$tpl = ob_get_clean();
			$_QI_DURACAO = round((microtime(true) - $_QI_INICIO) * 1000, 2);

			if ($_QI_COMENTARIO):
				$_QI_ARQUIVO = basename(dirname($_QI_ARQUIVO))."/".basename($_QI_ARQUIVO);
        $tpl = "\n<!-- INICIO $_QI_ARQUIVO => {$_QI_DURACAO}ms -->\n$tpl";
				$tpl .= "\n<!-- FIM $_QI_ARQUIVO => {$_QI_DURACAO}ms -->\n";
			endif;

			return $tpl;
		}catch(Exception $e){
			ob_end_clean();
			throw $e;
		}
  }
  
	/**
	* @param $arquivo string Coloca embrulha o arquivo atual, com o layout informado
	* no layout, deve existir uma variável chamada $TPL, que conterá o arquivo atual.
	* Esta função deve ser chamada antes de executar o template
	*/
	public static function layout($layout, $vars = array())
	{
		register_shutdown_function(
			array("Qi_Html", "_layout_shutdown_function"), 
			realpath($layout),
			$vars,
			debug_backtrace()
		);
		ob_start();
	}

	public static function _layout_shutdown_function($layout, $vars = array(), $backtrace = array())
	{
		$TPL = ob_get_clean();
		extract($vars);
		if (file_exists($layout)):
			include $layout;
		else:
			Qi_Http::modo_texto();
			echo "Arquivo de layout '$layout' não existe!\n";
			print_r($backtrace);
		endif;
	}
}

?>