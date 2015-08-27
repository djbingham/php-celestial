<?php
/**
 * @var Sloth\App $app
 * @var Sloth\Module\Resource\Base\ResourceDefinition $definition
 */
$autoAttribute = $definition->autoAttribute();
$primaryAttribute = $definition->primaryAttribute();
?>

<?php $renderAttributes = function (array $attributes) use ($autoAttribute, $primaryAttribute, &$renderAttributes) { ?>
    <ul>
        <?php foreach ($attributes as $attributeName => $attribute) { ?>
            <?php $details = array(); ?>
            <?php if ($attribute instanceof \Sloth\Module\Resource\Definition\AttributeList) { ?>
                <li>
                    <?= $attributeName ?> (list)
                    <?php $renderAttributes($attribute->getAll()); ?>
                </li>
            <?php } else { ?>
                <?php
                if ($attribute->getName() === $autoAttribute) {
                    $details[] = 'auto-increment';
                } elseif ($attribute->getName() === $primaryAttribute) {
                    $details[] = 'primary key';
                }
                ?>
                <li>
                    <?= $attribute->getName() ?>
                    <?php if (count($details) > 0) { ?>
                        <?= sprintf('(%s)', implode (', ', $details)) ?>
                    <?php } ?>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
<?php } ?>

<h2><?= $definition->name() ?></h2>

<?php $renderAttributes($definition->attributeList()->getAll()); ?>

<p>
    <a href="<?= $app->createUrl(array("resource", lcfirst($definition->name()))) ?>">
        Index
    </a>
    <br>
    <a href="<?= $app->createUrl(array("resource", lcfirst($definition->name()), "simpleSearch")) ?>">
        Simple Search
    </a>
    <br>
    <a href="<?= $app->createUrl(array("resource", lcfirst($definition->name()), "search")) ?>">
        Detailed Search
    </a>
    <br>
    <a href="<?= $app->createUrl(array("resource", lcfirst($definition->name()), "create")) ?>">
        Create
    </a>
    <br>
</p>
<p>
    <a href="<?= $app->createUrl(array("resource", "index")) ?>">Resource Definition Index</a>
</p>
