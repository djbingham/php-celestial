<?php
use \Sloth\Module\Resource\Definition\Attribute;
use \Sloth\Module\Resource\Definition\AttributeList;

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
function renderAttributes(AttributeList $attributes, array $ancestors = array())
{
	$html = "";
	foreach ($attributes as $attribute) {
		if ($attribute instanceof AttributeList) {
			$html .= renderAttributeSubList($attribute, $ancestors);
		} else {
			$html .= renderAttribute($attribute);
		}
	}
	return $html;
}

function renderAttribute(Attribute $attribute)
{
	return sprintf('<li>%s</li>', $attribute->name);
}

function renderAttributeSubList(AttributeList $attributes, array $ancestors)
{
	$sectionTitle = $attributes->name;
	if (count($ancestors) > 0) {
		foreach ($ancestors as $ancestor) {
			$sectionTitle .= sprintf(' of %s', $ancestor);
		}
	}
	$ancestors[] = $attributes->name;
	$html = sprintf('<h3>%s</h3>', $sectionTitle);
	$html .= sprintf('<ul>%s</ul>', renderAttributes($attributes, $ancestors));
	return $html;
}
?>
