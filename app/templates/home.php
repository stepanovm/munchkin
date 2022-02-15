<?php
/** @var \Lynxx\View $this */
/** @var string $name user name */

$this->setLayout('main.php');

$this->registerComponent('tempComponent', 'temp_component.php');

$this->registerJs('/js/jquery-3.5.1.min.js', ['async', 'nocompress']);
$this->registerJs('/js/home.js', ['async']);
$this->registerCss('/css/main.css');
?>

<p>Hello, <?=$name?></p>

<?php

$js = file_get_contents(__DIR__ . '/../../web/js/test.js');
$compressedJs = \WebSharks\JsMinifier\Core::compress($js);
var_dump($compressedJs);

