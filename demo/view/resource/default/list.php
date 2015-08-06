<?php
/**
 * @var DemoGraph\Module\Graph\ResourceList $resourceList
 * @var DemoGraph\Module\Graph\Resource $resource
 */
?>
<h2>Resource List</h2>
<dl>
	<?php foreach ($resources as $index => $resource) { ?>

		<dt>Item #<?= $index ?></dt>
		<dd><ul>
			<?php foreach ($resource->getAttributes() as $attributeName => $attributeValue) { ?>
				<li><?= $attributeName ?>: <?= $attributeValue ?></li>
			<?php } ?>
		</ul></dd>

	<?php } ?>
</dl>
