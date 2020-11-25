<?php
/** @var \Lynxx\View $this */
/** @var string $name user name */

$this->setLayout('main.php');

$this->registerJs('jquery-3.5.1.min.js', ['async', 'nocompress']);
$this->registerJs('home.js', ['async']);
?>

<p>Hello, <?=$name?></p>

