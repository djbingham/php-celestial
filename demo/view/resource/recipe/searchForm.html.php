<?php
/**
 * @var Sloth\App $app
 */
?>
<form action="<?= $app->createUrl(array('resource', 'recipe', 'search')) ?>" method="get">
    <h2>Search Recipes</h2>
    <div>
        <label for="id">ID</label>
        <input name="filters[0][subject]" type="hidden" value="id">
        <select name="filters[0][comparator]">
            <option value="=">=</option>
            <option value="!=">!=</option>
            <option value="<"><</option>
            <option value=">">></option>
            <option value="<="><=</option>
            <option value=">=">>=</option>
        </select>
        <input name="filters[0][value]" type="number">
    </div>
    <br>
    <div>
        <label for="name">Name</label>
        <input name="filters[1][subject]" type="hidden" value="name">
        <select name="filters[1][comparator]">
            <option value="=">=</option>
            <option value="!=">!=</option>
        </select>
        <input name="filters[1][value]" type="text">
    </div>
    <br>
    <div>
        <label for="description">Description</label>
        <input name="filters[2][subject]" type="hidden" value="description">
        <select name="filters[2][comparator]">
            <option value="=">=</option>
            <option value="!=">!=</option>
        </select>
        <input name="filters[2][value]" type="text">
    </div>
    <br>
    <button type="submit">Search</button>
</form>
