<?php
use \Sloth\Module\Graph\Definition\Table;

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
<form action="<?= $app->createUrl(array('graph', lcfirst($resourceDefinition->name), $resource->getAttribute($resourceDefinition->primaryAttribute), 'put')) ?>" method="post">
	<?= renderAttributeListInputs($resourceDefinition->attributes, $resourceDefinition->table, $resource->getAttributes()) ?>
	<button type="submit">Update</button>
</form>

<?php
function renderAttributeListInputs(array $attributes, Table $tableDefinition, array $data, array $ancestors = array())
{
	$html = "";
	foreach ($attributes as $attributeName => $include) {
		if ($include === true) {
			$html .= renderAttributeInput($attributeName, $data, $ancestors);
		} elseif (is_array($include)) {
			$childLink = $tableDefinition->links->getByName($attributeName);
			if (!in_array($childLink->onUpdate, array(Sloth\Module\Graph\Definition\Table\Join::ACTION_IGNORE, Sloth\Module\Graph\Definition\Table\Join::ACTION_REJECT))) {
				$subListAncestors = $ancestors;
				array_push($subListAncestors, $attributeName);
				$html .= renderAttributeSubListInputs($include, $childLink, $data[$attributeName], $subListAncestors);
			}
		}
	}
	return sprintf('<fieldset>%s</fieldset>', $html);
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

function renderAttributeSubListInputs(array $attributes, Table\Join $tableLink, array $data, array $ancestors)
{
	$parentName = array_pop($ancestors);
	$sectionTitle = $parentName;
	if (count($ancestors) > 0) {
		foreach (array_reverse($ancestors) as $ancestor) {
			$sectionTitle .= sprintf(' of %s', $ancestor);
		}
	}
	$ancestors[] = $parentName;
	$lastAncestorIndex = count($ancestors);

	$html = sprintf('<h3>%s</h3>', $sectionTitle);

	if (in_array($tableLink->type, array(Table\Join::ONE_TO_MANY))) {
		foreach ($data as $rowIndex => $row) {
			$ancestors[$lastAncestorIndex] = $rowIndex;
			$html .= renderAttributeListInputs($attributes, $tableLink->getChildTable(), $row, $ancestors);
		}
	} elseif ($tableLink->type === Table\Join::MANY_TO_MANY) {
		$linkFields = $tableLink->getLinkedFields();
		$editableAttributes = array(
			$linkFields['child']->name => $attributes[$linkFields['child']->name]
		);

		foreach ($data as $rowIndex => $row) {
			$ancestors[$lastAncestorIndex] = $rowIndex;
			/** @var Table\Join $childLink */
			foreach ($tableLink->getChildTable()->links as $childLink) {
				if (array_key_exists($childLink->name, $attributes)) {
					$editableAttributes[$childLink->name] = $attributes[$childLink->name];
				}
			}
			$html .= renderAttributeListInputs($editableAttributes, $tableLink->getChildTable(), $row, $ancestors);
		}
	} else {
		$html .= renderAttributeListInputs($attributes, $tableLink->getChildTable(), $data, $ancestors);
	}
	return sprintf('<fieldset>%s</fieldset>', $html);
}
?>
