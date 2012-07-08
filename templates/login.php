<?php use CampusBoard\Csrf; use CampusBoard\View; ?>
<h1>Log in</h1>
<?php if ($error): ?><p class="error"><?= View::e($error) ?></p><?php endif; ?>
<form method="post" class="stack narrow">
    <?= Csrf::field() ?>
    <label>Email <input type="email" name="email" required autofocus></label>
    <label>Password <input type="password" name="password" required></label>
    <button type="submit" class="btn">Log in</button>
</form>
<p>New here? <a href="/register">Create an account</a></p>
