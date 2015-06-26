<?php
/**
 * @var Sloth\Module\Resource\Base\Resource $resource
 */
?>
<h2>Recipe <?= $resource->getAttribute('id') ?></h2>
<h3><?= $resource->getAttribute('name') ?></h3>
<p><?= $resource->getAttribute('description') ?></p>
<p>
    <a href="<?= $this->app->createUrl(array("resource", 'recipe')) ?>">
        Index
    </a>
    <br>
    <a href="<?= $this->app->createUrl(array("resource", 'recipe', $resource->getAttribute('name'), "update")) ?>">
        Edit
    </a>
    <br>
</p>