<?php
/**
 * @var Sloth\App $app
 * @var Sloth\Module\Resource\Base\ResourceList $resourceList
 */
?>
<h2>Recipe List</h2>
<p>
    <a href="<?= $app->createUrl(array('resource', 'recipe', 'create')) ?>">Create</a>
    <br>
    <a href="<?= $app->createUrl(array('resource', 'recipe', 'simpleSearch')) ?>">Simple Search</a>
    <br>
    <a href="<?= $app->createUrl(array('resource', 'recipe', 'search')) ?>">Detailed Search</a>
    <br>
</p>
<dl>
<?php for ($i = 0; $i < $resourceList->count(); $i++) {
    $resource = $resourceList->get($i);
    $authors = $resource->getAttribute('authors');
    $ingredients = $resource->getAttribute('ingredients');
    $steps = $resource->getAttribute('steps'); ?>
    <dt>
        <?= $resource->getAttribute('id') ?>. <?= $resource->getAttribute('name') ?>
        (
        <a href="<?= $app->createUrl(array('resource', 'recipe', $resource->getAttribute('name'))) ?>">
            View
        </a>,
        <a href="<?= $app->createUrl(array('resource', 'recipe', $resource->getAttribute('name'), 'update')) ?>">
            Edit
        </a>
        )
    </dt>
    <dd>
        <?= $resource->getAttribute('description') ?>
        <?php if (is_array($authors)) { ?>
            <h3>Authors</h3>
            <ul>
                <?php foreach($authors as $index => $author) { ?>
                    <li><?= $author['name'] ?></li>
                <?php } ?>
            </ul>
        <?php } ?>
        <?php if (is_array($ingredients)) { ?>
            <h3>Ingredients</h3>
            <ul>
                <?php foreach($ingredients as $index => $ingredient) { ?>
                    <li><?= $ingredient['name'] ?></li>
                <?php } ?>
            </ul>
        <?php } ?>
        <?php if (is_array($steps)) { ?>
            <h3>Steps</h3>
            <ul>
                <?php foreach($steps as $index => $step) { ?>
                    <li><?= $step['number'] ?>: <?= $step['instruction'] ?></li>
                <?php } ?>
            </ul>
        <?php } ?>
    </dd>
<?php } ?>
</dl>