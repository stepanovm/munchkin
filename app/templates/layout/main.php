<?php
/** @var string $content rendered template */
/** @var \Lynxx\View $this */
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title><?= $this->getTitle(); ?></title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= $this->getHeads(); ?>
</head>
<body>

<div class="header">HEADER</div>

<div>SOURCE: <?= $this->templatePath ?></div>
<?= $content ?>

<div class="footer">FOOTER</div>

</body>
</html>