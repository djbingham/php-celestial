<?php
/**
 * @var Sloth\App $app
 */
?>
<form action="<?= $app->createUrl(array('resource', 'derivation')) ?>" method="post">
    <div>
        <label for="name">Name</label>
        <input name="name" type="text">
    </div>
    <br>
    <div>
        <label for="description">Description</label>
        <input name="description">
    </div>
    <br>
    <button type="submit">Create</button>
</form>
