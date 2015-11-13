<?php
use Sloth\Module\Resource\Definition\Table;
use Sloth\Module\Resource\Definition\Attribute;
use Sloth\Module\Resource\Definition\AttributeList;

/**
 * @var Sloth\App $app
 * @var array $data
 */

/** @var Sloth\Module\Resource\Definition\Resource $resourceDefinition */
$resourceDefinition = $data['resourceDefinition'];
$resourceName = lcfirst($resourceDefinition->name);
?>
<h2>Create Resources (<?= ucfirst($resourceName) ?>)</h2>
<p>
	<a href="<?= $app->createUrl(array('resource', 'definition', $resourceName)) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'view', $resourceName)) ?>"><?= ucfirst($resourceName) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'search', $resourceName)) ?>"><?= ucfirst($resourceName) ?> Search</a>
</p>
<form action="<?= $app->createUrl(array('resource', 'create', lcfirst($resourceName))) ?>" method="post">
	<?= renderAttributeListInputs($resourceDefinition->attributes, $resourceDefinition->table) ?>
	<button type="submit">Create</button>
</form>

<?php
function renderAttributeListInputs(AttributeList $attributes, Table $tableDefinition, array $ancestors = array(), $index = null)
{
	$html = "";
	/** @var Attribute|AttributeList $attribute */
	foreach ($attributes as $attribute) {
		if ($attribute instanceof AttributeList) {
			$subListAncestors = $ancestors;
			array_unshift($subListAncestors, $attribute->name);
			$join = $tableDefinition->links->getByName($attribute->name);
			if (in_array($join->onInsert, array(Table\Join::ACTION_INSERT))) {
				$html .= renderAttributeSubListInputs($attribute, $subListAncestors, $join);
			}
		} else {
			$tableField = $tableDefinition->fields->getByName($attribute->name);
			if ($tableField->autoIncrement === false) {
				$html .= renderAttributeInput($attribute->name, $ancestors, $index);
			}
		}
	}
	return $html;
}

function renderAttributeInput($attributeName, $ancestors, $index = false)
{
	if (!empty($ancestors)) {
		$inputName = '';
		$inputName .= array_shift($ancestors);

		foreach ($ancestors as $ancestor) {
			$inputName .= sprintf('[%s]', $ancestor);
		}

		if ($index !== null) {
			$inputName .= sprintf('[%s]', $index);
		}

		$inputName .= sprintf('[%s]', $attributeName);
	} else {
		$inputName = $attributeName;
	}
	return sprintf('<label>%s</label> <input name="%s"><br><br>', $attributeName, $inputName);
}

function renderAttributeSubListInputs(AttributeList $attributes, array $ancestors, Table\Join $join)
{
	$childTable = $join->getChildTable();

	/** @var Table\Join\Constraint $constraint */
	foreach ($join->getConstraints() as $constraint) {
		$childFieldName = $constraint->childField->name;
		if (property_exists($attributes, $childFieldName)) {
			unset($attributes[$childFieldName]);
		}
	}

	$parentName = array_pop($ancestors);
	$sectionTitle = $parentName;
	if (count($ancestors) > 0) {
		foreach ($ancestors as $ancestor) {
			$sectionTitle .= sprintf(' of %s', $ancestor);
		}
	}
	$ancestors[] = $parentName;


	$html = sprintf('<h3>%s</h3>', $sectionTitle);

	if ($join->type === Table\Join::MANY_TO_MANY || $join->type === Table\Join::ONE_TO_MANY) {
		$inputsHtml = '';
		for ($i = 0; $i < 2; $i++) {
			$thisInputsHtml = renderAttributeListInputs($attributes, $childTable, $ancestors, $i);
			$inputsHtml .= sprintf('<fieldset>%s</fieldset>', $thisInputsHtml);
		}
	} else {
		$thisInputsHtml = renderAttributeListInputs($attributes, $childTable, $ancestors);
		$inputsHtml = sprintf('<fieldset>%s</fieldset>', $thisInputsHtml);
	}
	$html .= $inputsHtml;

	return $html;
}
?>
