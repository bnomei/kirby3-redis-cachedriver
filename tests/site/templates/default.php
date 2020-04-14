<?php
/** @var DefaultPage $page */

?><h1><?= get_class($page) ?>: <?= $page->title() ?></h1>
<?= $page->text()->kirbytext() ?>

<time><?= date('c'); ?></time>
<hr>
<code><?php
    echo $page->dbsize();
?></code>
