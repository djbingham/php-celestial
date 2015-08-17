<?php
/**
 * @var Sloth\Module\Graph\ResourceList $resources
 */
?>
<h2>Resource List</h2>
<dl>
	<?php foreach ($resources as $index => $resource): ?>

		<dt>Resource #<?= $index ?></dt>
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
