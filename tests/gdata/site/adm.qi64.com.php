<?

$start = microtime(true);
header("Content-Type: text/plain");
require_once "../../setup.inc.php";
require_once "qi/gdata/site.php";
require_once "qi/error.php";

Qi_Error::converter_erro_em_excecao();

$gsite = Qi_Gdata_Site::carregar("audiowaystudio");

echo gsite_cfg("busca_palavras_chave");

$pagina = $gsite->pagina("agenda");
?>
<?= gsite_cfg("logo.png") ?>

<?= gsite_cfg("titulo_site") ?>

<?= $pagina ?>

<?= $pagina["caminho"] ?>

<?= $pagina->id ?>

<?= $pagina->conteudo ?>

<? print_r($pagina->campos) ?>

<? foreach($pagina as $subpagina): ?>
   <?= $subpagina["data"] ?> = <?= $subpagina->uf() ?> 
<? endforeach ?>

<? $pagina = $gsite->pagina("cfg") ?>

<? foreach($pagina->arquivos as $arquivo): ?>
   <?= ($arquivo->titulo) ?>  <?= ($arquivo->conteudo) ?> 
<? endforeach ?>

<? printf("\n%dms\n", (microtime(true) - $start)*1000) ?>
<? printf("\n%dKB\n", memory_get_peak_usage(true)/1024) ?>

<? //print_r(($r)) ?>

Entrys: 140
Roots sem cfg: 11
Paginas com pagename: 14
alternate: 133
sem alternate: 7