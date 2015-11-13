<?php
use Sloth\Module\Resource\Definition\Resource\Attribute;
use Sloth\Module\Resource\Definition\Resource\AttributeList;
use \Sloth\Module\Resource\Definition\Table;

/**
 * @var Sloth\App $app
 * @var array $data
 */

/** @var Sloth\Module\Resource\Definition\Resource $resourceDefinition */
$resourceDefinition = $data['resourceDefinition'];

/** @var array $resource */
$resource = $data['resource'];

$resourceName = lcfirst($resourceDefinition->name);
?>
<h2>Update Resource (<?= ucfirst($resourceName) ?>)</h2>
<p>
	<a href="<?= $app->createUrl(array('resource', 'definition', $resourceName)) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'view', $resourceName)) ?>"><?= ucfirst($resourceName) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'search', $resourceName)) ?>"><?= ucfirst($resourceName) ?> Search</a>
</p>
<form action="<?= $app->createUrl(array('resource', 'update', $resourceName, $resource[$resourceDefinition->primaryAttribute])) ?>" method="post">
	<?= renderAttributeListInputs($resourceDefinition->attributes, $resourceDefinition->table, $resource) ?>
	<button type="submit">Update</button>
</form>

<?php
function renderAttributeListInputs(AttributeList $attributes, Table $tableDefinition, array $data, array $ancestors = array())
{
	$html = "";
	/** @var AttributeList|Attribute $attribute */
	foreach ($attributes as $attribute) {
		if ($attribute instanceof AttributeList) {
			$childLink = $tableDefinition->links->getByName($attribute->name);
			if (!in_array($childLink->onUpdate, array(Sloth\Module\Resource\Definition\Table\Join::ACTION_IGNORE, Sloth\Module\Resource\Definition\Table\Join::ACTION_REJECT))) {
				$subListAncestors = $ancestors;
				array_push($subListAncestors, $attribute->name);
				$html .= renderAttributeSubListInputs($attribute, $childLink, $data[$attribute->name], $subListAncestors);
			}
		} else {
			$html .= renderAttributeInput($attribute->name, $data, $ancestors);
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

function renderAttributeSubListInputs(AttributeList $attributes, Table\Join $tableLink, array $data, array $ancestors)
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
		$childFieldName = $linkFields['child']->name;

		$editableAttributes = new AttributeList();
		$editableAttributes->push($attributes->getByProperty('name', $childFieldName));

		foreach ($data as $rowIndex => $row) {
			$ancestors[$lastAncestorIndex] = $rowIndex;

			/** @var Table\Join $childLink */
			foreach ($tableLink->getChildTable()->links as $childLink) {
				if ($attributes->indexOfPropertyValue('name', $childLink->name) !== -1) {
					$editableAttributes->push($attributes->getByProperty('name', $childLink->name));
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
