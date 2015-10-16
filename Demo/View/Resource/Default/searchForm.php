<?php
/**
 * @var Sloth\App $app
 * @var array $data
 */

/** @var Sloth\Module\Resource\Definition\Resource $resourceDefinition */
$resourceDefinition = $data['resourceDefinition'];

$resourceName = lcfirst($resourceDefinition->name);
?>
<h2>Search Resources (<?= ucfirst($resourceName) ?>)</h2>
<p>
	<a href="<?= $app->createUrl(array('resource', 'definition', $resourceName)) ?>">Definition</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'view', $resourceName)) ?>"><?= ucfirst($resourceName) ?> List</a>
	&nbsp;|&nbsp;
	<a href="<?= $app->createUrl(array('resource', 'filter', $resourceName)) ?>"><?= ucfirst($resourceName) ?> Filter</a>
</p>
<form action="<?= $app->createUrl(array('resource', 'search', lcfirst($resourceName))) ?>" method="get">
	<?= renderAttributeListInputs($resourceDefinition->attributes) ?>
	<button type="submit">Search</button>
</form>

<?php
function renderAttributeListInputs(array $attributes, array $ancestors = array(), &$index = 1)
{
	$html = "";
	foreach ($attributes as $attributeName => $include) {
		if ($include === true) {
			$html .= renderAttributeInput($attributeName, $ancestors, $index);
			$index++;
		} elseif (is_array($include)) {
			$html .= renderAttributeSubListInputs($attributeName, $include, $ancestors, $index);
		}
	}
	return $html;
}

function renderAttributeInput($attributeName, $ancestors, $index)
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

	$inputHtml = <<<EOT
<label>%2\$s</label>
<input name="filters[%1\$s][subject]" type="hidden" value="%3\$s">
<select name="filters[%1\$s][comparator]">
	<option value=""></option>
	<option value="=">=</option>
	<option value="!=">!=</option>
	<option value="<"><</option>
	<option value=">">></option>
	<option value="<="><=</option>
	<option value=">=">>=</option>
</select>
<input name="filters[%1\$s][value]" type="text">
<br><br>
EOT;
	$inputHtml = sprintf($inputHtml, $index, $attributeName, $inputName);

	return $inputHtml;
}

function renderAttributeSubListInputs($ancestorName, array $attributes, array $ancestors, &$index)
{
	$sectionTitle = $ancestorName;
	if (count($ancestors) > 0) {
		foreach ($ancestors as $ancestor) {
			$sectionTitle .= sprintf(' of %s', $ancestor);
		}
	}
	$ancestors[] = $ancestorName;
	$html = sprintf('<h3>%s</h3>', $sectionTitle);
	$html .= renderAttributeListInputs($attributes, $ancestors, $index);
	return $html;
}
?>
