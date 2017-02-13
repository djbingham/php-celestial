<?php
use Celestial\Module\Data\Resource\Definition\Resource\Attribute;
use Celestial\Module\Data\Resource\Definition\Resource\AttributeList;

/**
 * @var Celestial\App $app
 * @var array $data
 */

/** @var Celestial\Module\Data\Resource\Definition\Resource $resourceDefinition */
$resourceDefinition = $data['resourceDefinition'];

$resourceName = lcfirst($resourceDefinition->name);
?>
<h2>Search Resources (<?= $resourceName ?>)</h2>
<p>
	<a href="<?= $app->createUrl(array('resource', 'definition', $resourceName)) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'view', $resourceName)) ?>"><?= ucfirst($resourceName) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'search', $resourceName)) ?>"><?= ucfirst($resourceName) ?> Search</a>
</p>
<form action="<?= $app->createUrl(array('resource', 'filter', lcfirst($resourceName))) ?>" method="get">
	<?= renderAttributeListInputs($resourceDefinition->attributes) ?>
	<button type="submit">Search</button>
</form>

<?php
function renderAttributeListInputs(AttributeList $attributes, array $ancestors = array())
{
	$html = "";
	/** @var Attribute $attribute */
	foreach ($attributes as $attribute) {
		if ($attribute instanceof AttributeList) {
			$html .= renderAttributeSubListInputs($attribute->name, $attribute, $ancestors);
		} else {
			$html .= renderAttributeInput($attribute->name, $ancestors);
		}
	}
	return $html;
}

function renderAttributeInput($attributeName, $ancestors)
{
	if (!empty($ancestors)) {
		$inputName = '';
		$inputName .= array_shift($ancestors);
		foreach ($ancestors as $ancestor) {
			$inputName .= sprintf('.%s', $ancestor);
		}
		$inputName .= sprintf('.%s', $attributeName);
	} else {
		$inputName = $attributeName;
	}
	return sprintf('<label>%s</label> <input name="%s"><br><br>', $attributeName, $inputName);
}

function renderAttributeSubListInputs($ancestorName, AttributeList $attributes, array $ancestors)
{
	$sectionTitle = $ancestorName;
	if (count($ancestors) > 0) {
		foreach ($ancestors as $ancestor) {
			$sectionTitle .= sprintf(' of %s', $ancestor);
		}
	}
	$ancestors[] = $ancestorName;
	$html = sprintf('<h3>%s</h3>', $sectionTitle);
	$html .= renderAttributeListInputs($attributes, $ancestors);
	return $html;
}
?>
