<?php
/**
 * @var Sloth\App $app
 */
?>
<form action="<?= $app->createUrl(array('resource', 'recipe')) ?>" method="post">
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
    <div>
        <label for="steps[0][instruction]">First Instruction</label>
        <input name="steps[0][number]" placeholder="#" size="3" type="number">
        <input name="steps[0][instruction]" placeholder="Instruction">
        <input name="steps[0][notes]" placeholder="Notes">
    </div>
    <br>
    <div>
        <label for="steps[1][instruction]">Second Instruction</label>
        <input name="steps[1][number]" placeholder="#" size="3" type="number">
        <input name="steps[1][instruction]" placeholder="Instruction">
        <input name="steps[1][notes]" placeholder="Notes">
    </div>
    <br>
    <div>
        <label for="ingredients[0][name]">First Ingredient</label>
        <input name="ingredients[0][name]" placeholder="Name">
        <input name="ingredients[0][description]" placeholder="Description">
        <input name="ingredients[0][quantity]" placeholder="Quantity">
        <input name="ingredients[0][notes]" placeholder="Notes">
    </div>
    <br>
    <div>
        <label for="ingredients[1][name]">Second Ingredient</label>
        <input name="ingredients[1][name]" placeholder="Name">
        <input name="ingredients[1][description]" placeholder="Description">
        <input name="ingredients[1][quantity]" placeholder="Quantity">
        <input name="ingredients[1][notes]" placeholder="Notes">
    </div>
    <br>
    <div>
        <label for="authors[0][name]">Author</label>
        <input name="authors[0][name]" placeholder="Name">
    </div>
    <br>
    <button type="submit">Create</button>
</form>
<p>
    <a href="<?= $this->app->createUrl(array("resource", 'recipe')) ?>">
        Index
    </a>
    <br>
</p>