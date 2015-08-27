<?php
/**
 * @var Sloth\App $app
 * @var Sloth\Module\Resource\Base\Resource $resource
 */
?>
<h2>Recipe <?= $resource->getAttribute('id') ?> Deleted</h2>
<h3><?= $resource->getAttribute('name') ?></h3>
<p><?= $resource->getAttribute('description') ?></p>
<p>
    <a href="<?= $app->createUrl(array("resource", 'recipe')) ?>">
        Index
    </a>
    <br>
</p>