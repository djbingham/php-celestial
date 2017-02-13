<?php
/**
 * @var Celestial\App $app
 * @var array $data
 */

/** @var array $resource */
$resource = $data['resourceDefinition'];

/** @var array $errors */
$errors = $data['errors'];

$resourceName = lcfirst($resourceDefinition->name);
?>

<h2>Resource Validation: <?= ucfirst($resourceName) ?></h2>

<? if (!empty($errors)): ?>

	<span class="invalid">
		<h3>Errors</h3>
		<ul>
			<? foreach ($errors as $error): ?>
				<li><?= $error ?></li>
			<? endforeach; ?>
		</ul>
	</span>

<? else: ?>

	<span class="valid">No errors</span>

<? endif; ?>
