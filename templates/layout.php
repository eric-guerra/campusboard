<?php use CampusBoard\Auth; use CampusBoard\Csrf; use CampusBoard\View; $me = Auth::user(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CampusBoard</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="/">CampusBoard</a>
    <nav>
        <?php if ($me): ?>
            <a href="/listings/new" class="btn">Post a listing</a>
            <a href="/me">My listings</a>
            <form method="post" action="/logout" class="inline">
                <?= Csrf::field() ?>
                <button type="submit" class="link">Log out (<?= View::e($me['display_name']) ?>)</button>
            </form>
        <?php else: ?>
            <a href="/login">Log in</a>
            <a href="/register" class="btn">Sign up</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
    <?= $content ?>
</main>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="/assets/app.js"></script>
</body>
</html>
