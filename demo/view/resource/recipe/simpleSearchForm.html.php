<?php
/**
 * @var Sloth\App $app
 */
?>
<form action="<?= $app->createUrl(array('resource', 'recipe')) ?>" method="get">
    <h2>Search Recipes</h2>
    <div>
        <label for="id">ID</label>
        <input name="id" type="number">
    </div>
    <br>
    <div>
        <label for="name">Name</label>
        <input name="name" type="text">
    </div>
    <br>
    <div>
        <label for="description">Description</label>
        <input name="description" type="text">
    </div>
    <br>
    <button type="submit">Search</button>
</form>
