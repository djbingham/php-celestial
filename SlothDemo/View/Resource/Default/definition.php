<?php
/**
 * @var Sloth\App $app
 * @var array $data
 */

/** @var Sloth\Module\Resource\Definition\Resource $resourceDefinition */
$resourceDefinition = $data['resourceDefinition'];

$resourceName = lcfirst($resourceDefinition->name);
?>
<form action="<?= $app->createUrl(array('resource', lcfirst($resourceName) . '.php')) ?>" method="get">
	<h2>Resource Definition (<?= ucfirst($resourceName) ?>)</h2>
	<p>
		<a href="<?= $app->createUrl(array('resource', 'index')) ?>">Index</a>
		&nbsp;|&nbsp;
		<a href="<?= $app->createUrl(array('resource', 'view', $resourceName)) ?>"><?= ucfirst($resourceName) ?> List</a>
		&nbsp;|&nbsp;
		<a href="<?= $app->createUrl(array('resource', 'filter', $resourceName)) ?>">Filter</a>
		&nbsp;|&nbsp;
		<a href="<?= $app->createUrl(array('resource', 'search', $resourceName)) ?>">Search</a>
	</p>
	<?= renderAttributes($resourceDefinition->attributes) ?>
</form>

<?php
function renderAttributes(array $attributes, array $ancestors = array())
{
	$html = "";
	foreach ($attributes as $attributeName => $include) {
		if ($include === true) {
			$html .= renderAttribute($attributeName);
		} elseif (is_array($include)) {
			$html .= renderAttributeSubList($attributeName, $include, $ancestors);
		}
	}
	return $html;
}

function renderAttribute($attributeName)
{
	return sprintf('<li>%s</li>', $attributeName);
}

function renderAttributeSubList($ancestorName, array $attributes, array $ancestors)
{
	$sectionTitle = $ancestorName;
	if (count($ancestors) > 0) {
		foreach ($ancestors as $ancestor) {
			$sectionTitle .= sprintf(' of %s', $ancestor);
		}
	}
	$ancestors[] = $ancestorName;
	$html = sprintf('<h3>%s</h3>', $sectionTitle);
	$html .= sprintf('<ul>%s</ul>', renderAttributes($attributes, $ancestors));
	return $html;
}
?>
