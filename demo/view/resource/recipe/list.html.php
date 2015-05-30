<?php
/**
 * @var Sloth\Module\Resource\Base\ResourceList $resourceList
 */
?>
<h2>Recipe List</h2>
<dl>
<?php for ($i = 0; $i < $resourceList->count(); $i++) {
    $resource = $resourceList->get($i); ?>
    <dt><?= $resource->getAttribute('id') ?>. <?= $resource->getAttribute('name') ?></dt>
    <dd><?= $resource->getAttribute('description') ?></dd>
<?php } ?>
</dl>
