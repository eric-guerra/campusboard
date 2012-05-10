<?php use CampusBoard\Csrf; use CampusBoard\View;
$err = fn(string $f) => isset($errors[$f]) ? '<span class="error">' . View::e($errors[$f]) . '</span>' : ''; ?>
<h1>Post a listing</h1>
<form method="post" class="stack" id="listing-form">
    <?= Csrf::field() ?>
    <label>Title <input name="title" maxlength="120" required value="<?= View::e($data['title'] ?? '') ?>"></label>
    <?= $err('title') ?>
    <label>Category
        <select name="category_id" required>
            <option value="">Choose...</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (int)($data['category_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= View::e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?= $err('category_id') ?>
    <label>Price (USD, 0 for free) <input name="price" inputmode="decimal" value="<?= View::e($data['price'] ?? '') ?>"></label>
    <?= $err('price') ?>
    <label>Description <textarea name="body" rows="6" data-maxlength="2000"><?= View::e($data['body'] ?? '') ?></textarea></label>
    <span class="muted" id="body-count"></span>
    <?= $err('body') ?>
    <button type="submit" class="btn">Publish</button>
</form>
