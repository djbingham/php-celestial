<?php
/**
 * @var Sloth\App $app
 * @var Sloth\Module\Resource\Base\Resource $resource
 */
$resourceUrl = $app->createUrl(array(
    'resource',
    'recipe',
    str_replace(' ', '_', $resource->getAttribute('name')),
    'put'
));
?>
<form action="<?= $resourceUrl ?>" method="post">
    <h2>Update Recipe <?= $resource->getAttribute('id') ?></h2>
    <div>
        <label for="name">Name</label>
        <input name="name" type="text" value="<?= $resource->getAttribute('name') ?>">
    </div>
    <br>
    <div>
        <label for="description">Description</label>
        <input name="description" value="<?= $resource->getAttribute('description') ?>">
    </div>
    <br>
    <button type="submit">Update</button>
</form>
<p>
    <a href="<?= $app->createUrl(array("resource", 'recipe')) ?>">
        Index
    </a>
    <br>
    <a href="<?= $app->createUrl(array("resource", 'recipe', $resource->getAttribute('name'))) ?>">
        View
    </a>
    <br>
</p>