<?php
/**
 * @var Sloth\App $app
 * @var string $resourceName
 * @var Sloth\Module\Resource\Definition\Resource $resourceDefinition
 */
?>
<h2>Search Resources (<?= $resourceName ?>)</h2>
<p>
	<a href="<?= $app->createUrl(array('resource', $resourceName, 'definition')) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', $resourceName, 'list')) ?>"><?= ucfirst($resourceName) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', $resourceName, 'search')) ?>"><?= ucfirst($resourceName) ?> Search</a>
</p>
<form action="<?= $app->createUrl(array('resource', lcfirst($resourceName))) ?>" method="get">
	<?= renderAttributeListInputs($resourceDefinition->attributes) ?>
	<button type="submit">Search</button>
</form>

<?php
function renderAttributeListInputs(array $attributes, array $ancestors = array())
{
	$html = "";
	foreach ($attributes as $attributeName => $include) {
		if ($include === true) {
			$html .= renderAttributeInput($attributeName, $ancestors);
		} elseif (is_array($include)) {
			$html .= renderAttributeSubListInputs($attributeName, $include, $ancestors);
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
			$inputName .= sprintf('[%s]', $ancestor);
		}
		$inputName .= sprintf('[%s]', $attributeName);
	} else {
		$inputName = $attributeName;
	}
	return sprintf('<label>%s</label> <input name="%s"><br><br>', $attributeName, $inputName);
}

function renderAttributeSubListInputs($ancestorName, array $attributes, array $ancestors)
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
