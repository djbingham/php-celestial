<?php
/**
 * @var Sloth\App $app
 * @var Sloth\Module\Graph\Definition\Resource $resourceDefinition
 * @var Sloth\Module\Graph\Resource $resource
 */
?>
<h2>Update Resource (<?= $resourceDefinition->name ?>)</h2>
<p>
	<a href="<?= $app->createUrl(array('graph', $resourceDefinition->name, 'definition')) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('graph', $resourceDefinition->name, 'list')) ?>"><?= ucfirst($resourceDefinition->name) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('graph', $resourceDefinition->name, 'search')) ?>"><?= ucfirst($resourceDefinition->name) ?> Search</a>
</p>
<form action="<?= $app->createUrl(array('graph', lcfirst($resourceDefinition->name), 'put', $resource->getAttribute($resourceDefinition->primaryAttribute))) ?>" method="post">
	<?= renderAttributeListInputs($resourceDefinition->attributes, $resourceDefinition->table, $resource->getAttributes()) ?>
	<button type="submit">Update</button>
</form>

<?php
function renderAttributeListInputs(array $attributes, \Sloth\Module\Graph\Definition\Table $tableDefinition, array $data, array $ancestors = array())
{
	$html = "";
	foreach ($attributes as $attributeName => $include) {
		// TODO: Check link types for one-to-many links - render those as a list
		if ($include === true) {
			$html .= renderAttributeInput($attributeName, $data, $ancestors);
		} elseif (is_array($include)) {
			$childLink = $tableDefinition->links->getByName($attributeName);
			$subListAncestors = $ancestors;
			array_unshift($subListAncestors, $attributeName);
			$html .= renderAttributeSubListInputs($include, $childLink, $data[$attributeName], $subListAncestors);
		}
	}
	return $html;
}

function renderAttributeInput($attributeName, array $data, $ancestors)
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
	$attributeValue = $data[$attributeName];
	$htmlTemplate = '<label>%s</label> <input name="%s" value="%s"><br><br>';
	return sprintf($htmlTemplate, $attributeName, $inputName, $attributeValue);
}

function renderAttributeSubListInputs(array $attributes, \Sloth\Module\Graph\Definition\Table\Join $tableLink, array $data, array $ancestors)
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

	if (in_array($tableLink->type, array(\Sloth\Module\Graph\Definition\Table\Join::ONE_TO_MANY, \Sloth\Module\Graph\Definition\Table\Join::MANY_TO_MANY))) {
		foreach ($data as $row) {
			$html .= renderAttributeListInputs($attributes, $tableLink->getChildTable(), $row, $ancestors);
		}
	} else {
		$html .= renderAttributeListInputs($attributes, $tableLink->getChildTable(), $data, $ancestors);
	}
	return $html;
}
?>
