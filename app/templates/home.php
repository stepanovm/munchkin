<?php
/** @var \Lynxx\View $this */
/** @var string $name user name */

$this->setLayout('main.php');

$this->registerJs('/js/jquery-3.5.1.min.js', ['async', 'nocompress']);
$this->registerJs('/js/home.js', ['async']);
?>

<p>Hello, <?=$name?></p>

