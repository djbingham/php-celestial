<?php
/**
 * @var Celestial\App $app
 * @var array $data
 */

/** @var Celestial\Module\Data\Resource\ResourceList $resources */
$resources = $data['resources'];

/** @var Celestial\Module\Data\Resource\Definition\Resource $resourceDefinition */
$resourceDefinition = $data['resourceDefinition'];

$resourceName = lcfirst($resourceDefinition->name);
?>
<h2>Resource List</h2>
<p>
	<a href="<?= $app->createUrl(array('resource', 'definition', $resourceName)) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'filter', $resourceName)) ?>">Filter</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'search', $resourceName)) ?>">Search</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'create', $resourceName)) ?>">Create</a>
</p>
<dl>
	<?php
		/** @var Celestial\Module\Data\Resource\Resource $resource */
		foreach ($resources as $index => $resource):
	?>

		<dt>
			<a href="<?= $app->createUrl(array('resource', 'view', $resourceName, $resource[$resourceDefinition->primaryAttribute])) ?>">
				Resource #<?= $index ?>
			</a>
		</dt>
		<dd>
			<a href="<?= $app->createUrl(array('resource', 'delete', $resourceName, $resource[$resourceDefinition->primaryAttribute])) ?>">
				Delete
			</a>
			<?= renderAttributeList($resource); ?>
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
