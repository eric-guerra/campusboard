<?php use CampusBoard\Csrf; use CampusBoard\View;
$err = fn(string $f) => isset($errors[$f]) ? '<span class="error">' . View::e($errors[$f]) . '</span>' : ''; ?>
<h1>Create an account</h1>
<form method="post" class="stack narrow">
    <?= Csrf::field() ?>
    <label>University email <input type="email" name="email" required value="<?= View::e($data['email']) ?>"></label>
    <?= $err('email') ?>
    <label>Display name <input name="display_name" required maxlength="80" value="<?= View::e($data['display_name']) ?>"></label>
    <?= $err('display_name') ?>
    <label>Password <input type="password" name="password" required minlength="8"></label>
    <?= $err('password') ?>
    <button type="submit" class="btn">Sign up</button>
</form>
