<?php
use Sloth\Module\Data\Resource\Definition\Resource\Attribute;
use Sloth\Module\Data\Resource\Definition\Resource\AttributeList;
use Sloth\Module\Data\Table\Face\TableInterface;
use Sloth\Module\Data\Table\Face\JoinInterface;
use Sloth\Module\Data\ResourceDataValidator\Result\ExecutedValidator;
use Sloth\Module\Data\ResourceDataValidator\Result\ExecutedValidatorList;

/**
 * @var Sloth\App $app
 * @var array $data
 * @var ExecutedValidatorList $failedValidators
 */

/** @var Sloth\Module\Data\Resource\Definition\Resource $resourceDefinition */
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
	<?= renderAttributeListInputs($resourceDefinition->attributes, $resourceDefinition->table, $resource, $failedValidators) ?>
	<button type="submit">Update</button>
</form>

<?php
function renderAttributeListInputs(
	AttributeList $attributes,
	TableInterface $tableDefinition,
	array $data,
	ExecutedValidatorList $failedValidators,
	array $ancestors = array()
) {
	$html = "";
	/** @var AttributeList|Attribute $attribute */
	foreach ($attributes as $attribute) {
		if ($attribute instanceof AttributeList) {
			$childLink = $tableDefinition->links->getByName($attribute->name);
			if (!in_array($childLink->onUpdate, array(Sloth\Module\Data\Table\Definition\Table\Join::ACTION_IGNORE, Sloth\Module\Data\Table\Definition\Table\Join::ACTION_REJECT))) {
				$subListAncestors = $ancestors;
				array_push($subListAncestors, $attribute->name);
				$html .= renderAttributeSubListInputs($attribute, $childLink, $data[$attribute->name], $failedValidators, $subListAncestors);
			}
		} else {
			$html .= renderAttributeInput($attribute->name, $data, $failedValidators, $ancestors);
		}
	}
	return sprintf('<fieldset>%s</fieldset>', $html);
}

function renderAttributeInput($attributeName, array $data, ExecutedValidatorList $failedValidators, $ancestors)
{
	if (!empty($ancestors)) {
		$inputName = 'attributes';
		$validatorFieldName = '';

		foreach ($ancestors as $ancestor) {
			$inputName .= sprintf('[%s]', $ancestor);
			$validatorFieldName .= sprintf('.%s', $ancestor);
		}

		$inputName .= sprintf('[%s]', $attributeName);
		$validatorFieldName .= sprintf('.%s', $attributeName);
	} else {
		$inputName = sprintf('attributes[%s]', $attributeName);
		$validatorFieldName = $attributeName;
	}

	$validatorFieldName = ltrim($validatorFieldName, '.');
	$attributeValue = $data[$attributeName];

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
	JoinInterface $tableLink,
	array $data,
	ExecutedValidatorList $failedValidators,
	array $ancestors)
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

	if (in_array($tableLink->type, array(JoinInterface::ONE_TO_MANY))) {
		foreach ($data as $rowIndex => $row) {
			$ancestors[$lastAncestorIndex] = $rowIndex;
			$html .= renderAttributeListInputs($attributes, $tableLink->getChildTable(), $row, $failedValidators, $ancestors);
		}
	} elseif ($tableLink->type === JoinInterface::MANY_TO_MANY) {
		$linkFields = $tableLink->getLinkedFields();
		$childFieldName = $linkFields['child']->name;

		$editableAttributes = new AttributeList();
		$editableAttributes->push($attributes->getByProperty('name', $childFieldName));

		foreach ($data as $rowIndex => $row) {
			$ancestors[$lastAncestorIndex] = $rowIndex;

			/** @var JoinInterface $childLink */
			foreach ($tableLink->getChildTable()->links as $childLink) {
				if ($attributes->indexOfPropertyValue('name', $childLink->name) !== -1) {
					$editableAttributes->push($attributes->getByProperty('name', $childLink->name));
				}
			}

			$html .= renderAttributeListInputs($editableAttributes, $tableLink->getChildTable(), $row, $failedValidators, $ancestors);
		}
	} else {
		$html .= renderAttributeListInputs($attributes, $tableLink->getChildTable(), $data, $failedValidators, $ancestors);
	}
	return sprintf('<fieldset>%s</fieldset>', $html);
}
?>
