<?php
/**
 * @var Sloth\App $app
 * @var Sloth\Module\Graph\Resource $resource
 */
?>
<h2>Resource #<?= $resource->getAttribute('id') ?></h2>
<p>
	<a href="<?= $app->createUrl(array('graph', 'index')) ?>">Resource Index</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('graph', $resourceName, 'list')) ?>"><?= ucfirst($resourceName) ?> List</a>
</p>
<dl>
	<?= renderAttributeList($resource->getAttributes()); ?>
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
