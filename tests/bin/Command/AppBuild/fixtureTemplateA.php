<?php

/** @var \Lynxx\View $this */

$this->registerJs('/js/fixture_a.js', []);
$this->registerJs('/js/fixture_b.js', ['async']);
$this->registerJs('/js/fixture_c.js', ['async']);
$this->registerJs('/js/fixture_d.js', ['async']);
$this->registerJs('/js/fixture_e.js', ['async', 'nocompress']);

$this->registerCss('/css/a.css');
$this->registerCss('/css/b.css');

