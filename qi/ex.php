<?

/**
* Especializa a Exception padr�o do PHP
* Infelizmente todos os m�todos padr�es s�o "final"!
*/
class Qi_Ex extends Exception
{
	/**
	* Adiciona os par�metros arquivo e linha
	*/
	public function __construct($mensagem = "", $codigo = 0, $arquivo = null, $linha = null)
	{
		parent::__construct($mensagem, $codigo);
		if ($linha !== null)
			$this->line = $linha;
		if ($arquivo !== null)
			$this->file = $arquivo;
	}

	/**
	* Filtra o m�todo final getTrace(), retirando da pilha o Qi_Error::error_handler
	* Este problema normalmente acontece em warnings, como session_start e file("nao_existo")
	*/
	public function getTraceFiltrado()
	{
		return self::filtrar_trace($this);
	}

	/**
	* Retorna o trace de uma exception, mas sem o error_handler
	*/
	public static function filtrar_trace(Exception $ex)
	{
		$filtrado = array();
		foreach($ex->getTrace() as $trace)
			if ( array_search("error_handler", $trace) == "function" &&
				 array_search("Qi_Error", $trace) == "class" &&
				 ! ( isset($trace["file"], $trace["line"]) )
				) continue;
			else $filtrado[] = $trace;
		return $filtrado;
	}
}

?>