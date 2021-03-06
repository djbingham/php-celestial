<?php
/**
 * @var Celestial\App $app
 * @var array $data
 */

/** @var array $resource */
$resource = $data['resource'];

/** @var Celestial\Module\Data\Resource\Definition\Resource $resourceDefinition */
$resourceDefinition = $data['resourceDefinition'];

$resourceName = lcfirst($resourceDefinition->name);
$primaryAttribute = $resourceDefinition->primaryAttribute;
$resourceId = $resource[$primaryAttribute];
?>
<h2>Resource #<?= $resourceId ?></h2>
<p>
	<a href="<?= $app->createUrl(array('resource', 'index')) ?>">Resource Index</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'view', $resourceName)) ?>"><?= ucfirst($resourceName) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'update', $resourceName, $resourceId)) ?>">Update</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'delete', $resourceName, $resourceId)) ?>">Delete</a>
</p>
<dl>
	<?= renderAttributeList($resource); ?>
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
