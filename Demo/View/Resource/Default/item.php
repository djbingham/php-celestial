<?php
/**
 * @var Sloth\App $app
 * @var string $resourceName
 * @var Sloth\Module\Resource\Resource $resource
 * @var Sloth\Module\Resource\Definition\Resource $resourceDefinition
 */
$primaryAttribute = $resourceDefinition->primaryAttribute;
$resourceId = $resource->getAttribute($primaryAttribute);
?>
<h2>Resource #<?= $resourceId ?></h2>
<p>
	<a href="<?= $app->createUrl(array('resource', 'index')) ?>">Resource Index</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', $resourceName, 'list')) ?>"><?= ucfirst($resourceName) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', $resourceName, 'update', $resourceId)) ?>">Update</a>
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
