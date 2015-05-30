<?php
/**
 * @var Sloth\Module\Resource\Base\ResourceDefinition $definition
 */
$autoAttribute = $definition->autoAttribute();
$primaryAttribute = $definition->primaryAttribute();
?>
<h2><?= $definition->name() ?></h2>
<ul>
    <?php foreach ($definition->attributes() as $attributeName => $attributeAlias) { ?>
        <?php
            $details = array();
            if ($attributeName === $autoAttribute) {
                $details[] = 'auto-increment';
            }
            if ($attributeName === $primaryAttribute) {
                $details[] = 'primary key';
            }
        ?>
        <li>
            <?= $attributeName ?>
            <?php if (count($details) > 0) { ?>
                <?= sprintf('(%s)', implode (', ', $details)) ?>
            <?php } ?>
        </li>
    <?php } ?>
</ul>
