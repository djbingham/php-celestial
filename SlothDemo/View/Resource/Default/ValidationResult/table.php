<?php
use Sloth\Module\Validation\Face\ValidationErrorInterface;
use Sloth\Module\Validation\Face\ValidationErrorListInterface;

/**
 * @var Sloth\App $app
 * @var array $data
 */

/** @var \stdClass $table */
$table = $data['tableDefinition'];

/** @var ValidationErrorListInterface $errors */
$errors = $data['errors'];

/** @var string $tableName */
$tableName = $data['tableName'];
?>

<h2>Validation Report for Table `<?= ucfirst($tableName) ?>`</h2>

<h3>Errors</h3>

<?php
if ($errors->length() === 0) {
	echo '<span class="valid">No errors</span>';
} else {
	echo '<span class="invalid">' . renderErrorList($errors) . '</span>';
} ?>

<h3>Table Definition</h3>
<?= renderPropertyList($table) ?>

<?php
function renderErrorList(ValidationErrorListInterface $errorList)
{
	$html = '<ul>';

	/** @var ValidationErrorInterface $error */
	foreach ($errorList as $error) {
		$html .= '<li>' . renderError($error) . '</li>';
	}

	$html .= '</ul>';

	return $html;
}

function renderError(ValidationErrorInterface $error)
{
	$html = $error->getMessage();

	if ($error->getChildren()->length() > 0) {
		$html .= '<ul>';

		/** @var ValidationErrorInterface $child */
		foreach ($error->getChildren() as $child) {
			$html .= '<li>';
				$html .= $child->getMessage();

				if ($child->getChildren()->length() > 0) {
					$html .= renderErrorList($child->getChildren());
				}

			$html .= '</li>';
		}

		$html .= '</ul>';
	}

	return $html;
}

function renderPropertyList($list)
{
	$html = '<ul>';

	foreach ($list as $propertyName => $propertyValue) {
		if (is_object($propertyValue) || is_array($propertyValue)) {
			$html .= '<li>' . $propertyName . ': ' . renderPropertyList($propertyValue) . '</li>';
		} else {
			$html .= '<li>' . $propertyName . ': ' . $propertyValue . '</li>';
		}
	}

	$html .= '</ul>';

	return $html;
}
