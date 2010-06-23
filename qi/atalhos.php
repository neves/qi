<?

/**
* Faz um texto ser seguro para inserir no html,
* aplicando htmlspecialchars e outros mais
*/
function h($valor)
{
	return Qi_Html::h($valor);
}

/**
* retorna checked="checked" caso verdadeiro
* $valor pode ser um boolean direto
* caso $comparacao seja informado, $valor será comparado com ele
* se $valor não for booleano, compara com strings padrões que são consideradas true
* pelo array Qi_Util::$VALORES_VERDADEIROS
*/
function c($valor, $comparacao = null)
{
	return Qi_Html::checked($valor, $comparacao);
}

/**
* mesmo acima, mas retorna selected="selected"
*/
function s($valor, $comparacao = null)
{
	return Qi_Html::selected($valor, $comparacao);
}

/**
* Cria um array apartir de uma lista em string, separada por espaços, vírgula, etc
* pode ser chamado com um valor:
  w("php 123 null fim") = array("php", "123", "null", "fim")
* ou com uma lista, assim:
* w("php", 123, null, "fim") = array("php", 123, null, "fim")
*/
function w($lista)
{
	if (func_num_args() > 1)
		return Qi_Util::to_a(func_get_args());
	else
		return Qi_Util::to_a($lista);
}

/**
 * Converte em array, mas por padrao, copia os valores para chaves.
 */
function to_a($lista, $values_as_keys = true)
{
	return Qi_Util::to_a($lista, $values_as_keys);
}

/**
* Transforma algo como, "data_nascimento" em "Data Nascimento"
*/
function r($palavra)
{
	return Qi_Util::rotulo($palavra);
}

function u($expressao)
{
	return new Qi_Db_Unescape($expressao);
}

?>