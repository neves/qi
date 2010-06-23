<?

require_once __DIR__."/ex.php";

class Qi_Error
{
	/**
	* Captura os erros gerados e dispara uma exceзгo
	*/
	public static function converter_erro_em_excecao()
	{
		set_error_handler(array("Qi_Error", "error_handler"));
	}

  public static function pp()
  {
 		ob_start();
		set_exception_handler(array("Qi_Error", "exception_handler"));
  }

	/**
	* Extrai um array com as linhas de cуdigo do arquivo.
	* Cada linha vem com a quebra de linha no final.
	* O нndice do array й exatamente a linha do arquivo
	*/
	public static function extrair_linhas_do_codigo_fonte($arquivo, $linha, $linhas_antes = 3, $linhas_depois = 3)
	{
		$linhas = file($arquivo, FILE_USE_INCLUDE_PATH);
		// caso as linhas anteriores sejam menores que 1, trava o inнcio na linha 1
		$inicio = max($linha - $linhas_antes, 1);
		$fim = min($linha + $linhas_depois, count($linhas));
		$tamanho = $fim - $inicio + 1; // +1 porque a primeira linha й a 1, nгo zero
		$linhas = array_slice($linhas, $inicio - 1, $tamanho, true);// -1 pois o array comeзa no zero
		// ajusta o array para a chave ser exatamente o nъmero da linha no arquivo fonte
		$intervalo = range($inicio, $fim);
		$linhas = array_combine($intervalo, $linhas);
		return $linhas;
	}

	public static function extrair_codigo_fonte($arquivo, $linha, $linhas_antes = 3, $linhas_depois = 3)
	{
		$linhas = self::extrair_linhas_do_codigo_fonte($arquivo, $linha, $linhas_antes, $linhas_depois);
		$codigo = "";
		foreach($linhas as $numero=>$linha)
			$codigo .= sprintf("%03d|%s", $numero, $linha);
		return $codigo;
	}

	/**
	* Exception Handler
	* @todo tratar exceptions que nгo sejam Qi_Ex*
	* @todo mostrar se foi warning, notice, etc
	* @todo logar no arquivo error_log
	*/
	public static function exception_handler(Exception $exception)
	{
		if( ob_get_level() > 0 ) ob_end_clean();
		header("Content-Type: text/plain");
		echo $_SERVER["REQUEST_URI"]."\n"; // @TODO: colocar o caminho todo e fazer a versгo MS-DOS
		echo sprintf("\n[%s]\n", $exception->getMessage());
		$traces = $exception instanceof Qi_Ex 
				  ? $exception->getTraceFiltrado() 
				  : $exception->getTrace();
		foreach($traces as $trace):
			$linha = $trace["line"];
			$arquivo = $trace["file"];
			echo sprintf("\n[%3d - %s]\n", $linha, $arquivo);
			echo str_repeat("-", 79);
			echo "\n";
			echo trim(self::extrair_codigo_fonte($arquivo, $linha, 1, 1));
			//echo "\n";
			//echo str_repeat("+", 79);
			echo "\n\n";
		endforeach;
	}

	/**
	* Error Handler
	*/
	public static function error_handler($codigo, $mensagem, $arquivo, $linha)
	{
		if (error_reporting() == 0) return false; // ignora erros usando @
		if ($arquivo == __FILE__) return false; // ignora erros neste arquivo
		throw new Qi_Ex($mensagem, $codigo, $arquivo, $linha);
	}
}

?>