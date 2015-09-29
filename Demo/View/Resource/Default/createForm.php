<?php
use Sloth\Module\Resource\Definition\Table;
/**
 * @var Sloth\App $app
 * @var string $resourceName
 * @var Sloth\Module\Resource\Definition\Resource $resourceDefinition
 */
?>
<h2>Create Resources (<?= $resourceName ?>)</h2>
<p>
	<a href="<?= $app->createUrl(array('resource', $resourceName, 'definition')) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', $resourceName, 'list')) ?>"><?= ucfirst($resourceName) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', $resourceName, 'search')) ?>"><?= ucfirst($resourceName) ?> Search</a>
</p>
<form action="<?= $app->createUrl(array('resource', lcfirst($resourceName))) ?>" method="post">
	<?= renderAttributeListInputs($resourceDefinition->attributes, $resourceDefinition->table) ?>
	<button type="submit">Create</button>
</form>

<?php
function renderAttributeListInputs(array $attributes, Table $tableDefinition, array $ancestors = array(), $index = null)
{
	$html = "";
	foreach ($attributes as $attributeName => $include) {
		if ($include === true) {
			$tableField = $tableDefinition->fields->getByName($attributeName);
			if ($tableField->autoIncrement === false) {
				$html .= renderAttributeInput($attributeName, $ancestors, $index);
			}
		} elseif (is_array($include)) {
			$subListAncestors = $ancestors;
			array_unshift($subListAncestors, $attributeName);
			$join = $tableDefinition->links->getByName($attributeName);
			if (in_array($join->onInsert, array(Table\Join::ACTION_INSERT))) {
				$html .= renderAttributeSubListInputs($include, $subListAncestors, $join);
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

function renderAttributeSubListInputs(array $attributes, array $ancestors, Table\Join $join)
{
	$childTable = $join->getChildTable();

	/** @var Table\Join\Constraint $constraint */
	foreach ($join->getConstraints() as $constraint) {
		$childFieldName = $constraint->childField->name;
		if (array_key_exists($childFieldName, $attributes)) {
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
