<?php
/**
 * @var Sloth\App $app
 * @var string $resourceName
 * @var Sloth\Module\Graph\ResourceList $resources
 */
?>
<h2>Resource List</h2>
<p>
	<a href="<?= $app->createUrl(array('graph', $resourceName, 'definition')) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('graph', $resourceName, 'filter')) ?>">Filter</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('graph', $resourceName, 'search')) ?>">Search</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('graph', $resourceName, 'create')) ?>">Create</a>
</p>
<dl>
	<?php foreach ($resources as $index => $resource): ?>

		<dt>
			<a href="<?= $app->createUrl(array('graph', $resourceName, $resource->getAttribute('id'))) ?>">
				Resource #<?= $index ?>
			</a>
		</dt>
		<dd>
			<?= renderAttributeList($resource->getAttributes()); ?>
		</dd>

	<?php endforeach ?>
</dl>

<?php
function renderAttributeList(array $attributes) {
	$string = "";
	foreach ($attributes as $attributeName => $attributeValue) {
		if (is_array($attributeValue)) {
			$attributeValue = renderAttributeList($attributeValue);
			if ($attributeValue === '') {
				$attributeValue = '<i>None</i>';
			}
		} elseif ($attributeValue === '') {
			$attributeValue = '<i>Null</i>';
		}
		$string .= "<li>{$attributeName}: {$attributeValue}</li>";
	}
	if (!empty($string)) {
		$string = sprintf("<ul>%s</ul>", $string);
	}
	return $string;
}
?>
