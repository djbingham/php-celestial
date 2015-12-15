<?php
use Sloth\Module\Data\Table\Definition\Table;
use Sloth\Module\Data\Resource\Definition\Resource\Attribute;
use Sloth\Module\Data\Resource\Definition\Resource\AttributeList;
use Sloth\Module\Data\ResourceDataValidator\Result\ExecutedValidator;
use Sloth\Module\Data\ResourceDataValidator\Result\ExecutedValidatorList;

/**
 * @var Sloth\App $app
 * @var array $data
 * @var array $presetData
 * @var ExecutedValidatorList $failedValidators
 */

/** @var Sloth\Module\Data\Resource\Definition\Resource $resourceDefinition */
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
	<?= renderAttributeListInputs($resourceDefinition->attributes, $resourceDefinition->table, $presetData, $failedValidators) ?>
	<button type="submit">Create</button>
</form>

<?php
function renderAttributeListInputs(
	AttributeList $attributes,
	Table $tableDefinition,
	array $presetData = array(),
	ExecutedValidatorList $failedValidators,
	array $ancestors = array(),
	$index = null
) {
	$html = "";
	/** @var Attribute|AttributeList $attribute */
	foreach ($attributes as $attribute) {
		if ($attribute instanceof AttributeList) {
			$subListAncestors = $ancestors;
			array_unshift($subListAncestors, $attribute->name);
			$join = $tableDefinition->links->getByName($attribute->name);
			if (in_array($join->onInsert, array(Table\Join::ACTION_INSERT))) {
				$html .= renderAttributeSubListInputs($attribute, $presetData, $failedValidators, $subListAncestors, $join);
			}
		} else {
			$tableField = $tableDefinition->fields->getByName($attribute->name);
			if ($tableField->autoIncrement === false) {
				$html .= renderAttributeInput($attribute->name, $presetData, $failedValidators, $ancestors, $index);
			}
		}
	}
	return $html;
}

function renderAttributeInput(
	$attributeName,
	array $presetData = array(),
	ExecutedValidatorList $failedValidators,
	$ancestors,
	$index = false
) {
	if (!empty($ancestors)) {
		$inputName = 'attributes';
		$validatorFieldName = '';

		foreach ($ancestors as $ancestor) {
			$inputName .= sprintf('[%s]', $ancestor);
			$validatorFieldName .= sprintf('.%s', $ancestor);
		}

		$validatorFieldName = ltrim($validatorFieldName, '.');

		if ($index !== null) {
			$inputName .= sprintf('[%s]', $index);
		}

		$inputName .= sprintf('[%s]', $attributeName);
		$validatorFieldName .= sprintf('.%s', $attributeName);
	} else {
		$inputName = sprintf('attributes[%s]', $attributeName);
		$validatorFieldName = $attributeName;
	}

	$attributeValue = array_key_exists($attributeName, $presetData) ? $presetData[$attributeName] : null;

	$errors = array();
	/** @var ExecutedValidator $failedValidator */
	foreach ($failedValidators->getByFieldName($validatorFieldName) as $failedValidator) {
		$errors = array_merge($errors, $failedValidator->getResult()->getErrors()->getMessages());
	}
	$errorText = implode('</span><span class="error">', $errors);

	$htmlTemplate = '<label>%s</label> <input name="%s" value="%s"> <span class="error">%s</span><br><br>';

	return sprintf($htmlTemplate, $attributeName, $inputName, $attributeValue, $errorText);
}

function renderAttributeSubListInputs(
	AttributeList $attributes,
	array $presetData,
	ExecutedValidatorList $failedValidators,
	array $ancestors,
	Table\Join $join
) {
	$childTable = $join->getChildTable();

	/** @var Table\Join\Constraint $constraint */
	foreach ($join->getConstraints() as $constraint) {
		$childFieldName = $constraint->childField->name;
		$attributes->removeByPropertyValue('name', $childFieldName);
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
			if (array_key_exists($join->name, $presetData) && array_key_exists(0, $presetData[$join->name])) {
				$childRowData = $presetData[$join->name][$i];
			} else {
				$childRowData = array();
			}
			$thisInputsHtml = renderAttributeListInputs($attributes, $childTable, $childRowData, $failedValidators, $ancestors, $i);
			$inputsHtml .= sprintf('<fieldset>%s</fieldset>', $thisInputsHtml);
		}
	} else {
		$childData = array_key_exists($join->name, $presetData) ? $presetData[$join->name] : array();
		$thisInputsHtml = renderAttributeListInputs($attributes, $childTable, $childData, $failedValidators, $ancestors);
		$inputsHtml = sprintf('<fieldset>%s</fieldset>', $thisInputsHtml);
	}
	$html .= $inputsHtml;

	return $html;
}
?>
