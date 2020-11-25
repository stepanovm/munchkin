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

<h1>Munchkin Game</h1>
<?= $content ?>

</body>
</html>