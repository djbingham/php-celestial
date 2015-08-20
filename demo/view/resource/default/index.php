<?php
/**
 * @var Sloth\App $app
 * @var array $resources
 */
?>
<h1>Resource Index</h1>
<ul>
    <?php foreach ($resources as $resourceName) { ?>
        <li>
            <a href="<?= $app->createUrl(array('graph', lcfirst($resourceName), 'definition')) ?>">
                <?= ucfirst($resourceName) ?>
            </a>
        </li>
    <?php } ?>
</ul>