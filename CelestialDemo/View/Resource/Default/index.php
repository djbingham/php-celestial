<?php
/**
 * @var Celestial\App $app
 * @var array $data
 */

/** @var array $resourceNames */
$resourceNames = $data['resourceNames'];
?>
<h1>Resource Index</h1>
<ul>
	<?php foreach ($resourceNames as $resourceName) { ?>
		<li>
			<a href="<?= $app->createUrl(array('resource', 'definition', lcfirst($resourceName))) ?>">
				<?= ucfirst($resourceName) ?>
			</a>
		</li>
	<?php } ?>
</ul>