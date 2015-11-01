<?php
/**
 * @var Sloth\App $app
 * @var array $data
 */

/** @var Sloth\Module\Resource\ResourceList $resources */
$resources = $data['resources'];

/** @var Sloth\Module\Resource\Definition\Resource $resourceDefinition */
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
		/** @var Sloth\Module\Resource\Resource $resource */
		foreach ($resources as $index => $resource):
	?>

		<dt>
			<a href="<?= $app->createUrl(array('resource', 'view', $resourceName, $resource['id'])) ?>">
				Resource #<?= $index ?>
			</a>
		</dt>
		<dd>
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
