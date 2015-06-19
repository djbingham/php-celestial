<?php
/**
 * @var Sloth\Module\Resource\Base\ResourceDefinition $definition
 */
$autoAttribute = $definition->autoAttribute();
$primaryAttribute = $definition->primaryAttribute();
?>
<h2><?= $definition->name() ?></h2>
<ul>
    <?php foreach ($definition->attributeList()->getAll() as $attributeName => $attribute) { ?>
        <?php $details = array(); ?>
        <?php if ($attribute instanceof \Sloth\Module\Resource\Definition\AttributeList) { ?>
            <li>
                <?= $attributeName ?> (list)
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
