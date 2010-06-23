<?

$start = microtime(true);

require_once "../../setup.inc.php";
require_once "qi/gdata/site.php";
require_once "qi/error.php";
require_once "qi/html.php";
require_once "qi/atalhos.php";

Qi_Error::converter_erro_em_excecao();

Qi_Gdata_Site::carregar("ethus");

$marcas = gsite_pagina("portifolio/marcas");
$sites = gsite_pagina("servicos/sites");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />
  <title><?=h( gsite_cfg("titulo_site") )?></title>
  <meta name="description" content="<?= h( gsite_cfg("busca_descricao") ) ?>" />
  <meta name="keywords" content="<?= h( gsite_cfg("busca_palavras_chave") ) ?>" />
<style>
table {
  border-collapse: collapse;
}
td, th {
  border: 1px solid silver;
  vertical-align: top;
}

.sites-layout-hbox td {
  border: none;
}
a img {
  border: none;
}
</style>
</head>

<body>

  <h1><?=h( gsite_cfg("titulo_site") )?></h1>

  <table>
    <tr>
      <td>
  <ul>
    <? foreach(gsite_pagina() as $pagina): ?>
    <li><a href="#<?=h( $pagina->caminho )?>"><?=h( $pagina->titulo )?></a>
      <ul>
      <? foreach($pagina->sub_paginas as $sub_pagina): ?>
        <li><a href="#<?=h( $sub_pagina->caminho )?>"><?=h( $sub_pagina->titulo )?></a></li>
      <? endforeach ?>
      </ul>
    </li>
    <? endforeach ?>
  </ul>
      </td><td>

        <h2><?=h( $sites->titulo )?></h2>
        <?= $sites->conteudo ?>
        <table>
          <thead>
            <tr>
            <? foreach($sites->campos as $campo): ?>
              <th><?=h( $campo )?></th>
            <? endforeach ?>
            </tr>
          </thead>
          <tbody>
            <? foreach($sites as $site): ?>
            <tr>
              <? foreach($site as $valor): ?>
              <td><?= $valor ?></td>
              <? endforeach ?>
            </tr>
            <? endforeach ?>
        </table>

        <ul>
        <? foreach($marcas as $marca): ?>
          <li>
            <a href="<?= $marca->url(640, 480) ?>">
              <?= $marca->img(64, 64) ?>
              <?=$marca?> [<?=$marca->pasta?>]
            </a>
          </li>
        <? endforeach ?>
        </ul>
        
      </td><td>
     
        <ul>
        <? foreach($marcas->pastas as $nome => $pasta): ?>
          <li>
            <h4><?= $nome ?></h4>
            <ul>
              <? foreach($pasta as $arquivo): ?>
                <li><?= $arquivo->img(64, 64) ?> <?= $arquivo->link ?></li>
              <? endforeach ?>
            </ul>
          </li>
        <? endforeach ?>
        </ul>

      </td>
    </tr>
  </table>
  
  <?=h( gsite_cfg("rodape") )?>

</body>
</html>
