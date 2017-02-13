<?php
use Celestial\Module\Validation\Face\ValidationErrorInterface;
use Celestial\Module\Validation\Face\ValidationErrorListInterface;
use Celestial\Module\Validation\Face\ValidationResultInterface;

/**
 * @var Celestial\App $app
 * @var array $data
 */

/** @var array $validationResults */
$validationResults = $data['validationResults'];
?>

<h2>Table Manifest Validation Reports</h2>

<?php
/** @var ValidationResultInterface $validationResult */
foreach ($validationResults as $tableName => $validationResult) {
?>
	<h3><?= ucfirst($tableName) ?></h3>

	<?php
	if ($validationResult->isValid()) {
		echo '<span class="valid">No errors</span>';
	} else {
		echo '<span class="invalid">' . renderErrorList($validationResult->getErrors()) . '</span>';
	}
	?>
<?php
}
?>

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
