<?php
/**
 * @var Sloth\App $app
 * @var string $resourceName
 * @var Sloth\Module\Graph\Definition\Resource $resourceDefinition
 */
?>
<h2>Create Resources (<?= $resourceName ?>)</h2>
<p>
	<a href="<?= $app->createUrl(array('graph', $resourceName, 'definition')) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('graph', $resourceName, 'list')) ?>"><?= ucfirst($resourceName) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('graph', $resourceName, 'search')) ?>"><?= ucfirst($resourceName) ?> Search</a>
</p>
<form action="<?= $app->createUrl(array('graph', lcfirst($resourceName))) ?>" method="post">
	<?= renderAttributeListInputs($resourceDefinition->attributes) ?>
	<button type="submit">Create</button>
</form>

<?php
function renderAttributeListInputs(array $attributes, array $ancestors = array())
{
	$html = "";
	foreach ($attributes as $attributeName => $include) {
		if ($include === true) {
			$html .= renderAttributeInput($attributeName, $ancestors);
		} elseif (is_array($include)) {
			$subListAncestors = $ancestors;
			array_unshift($subListAncestors, $attributeName);
			$html .= renderAttributeSubListInputs($include, $subListAncestors);
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

function renderAttributeSubListInputs(array $attributes, array $ancestors)
{
	$parentName = array_pop($ancestors);
	$sectionTitle = $parentName;
	if (count($ancestors) > 0) {
		foreach ($ancestors as $ancestor) {
			$sectionTitle .= sprintf(' of %s', $ancestor);
		}
	}
	$ancestors[] = $parentName;

	$html = sprintf('<h3>%s</h3>', $sectionTitle);
	$html .= renderAttributeListInputs($attributes, $ancestors);
	return $html;
}
?>
