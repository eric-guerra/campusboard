<?php use CampusBoard\View; ?>
<form id="search" class="search" method="get" action="/">
    <input type="search" name="q" value="<?= View::e($q) ?>" placeholder="Search textbooks, furniture, rides..." autocomplete="off">
    <select name="category">
        <option value="">All categories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= View::e($c['slug']) ?>" <?= $c['slug'] === $category ? 'selected' : '' ?>><?= View::e($c['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Search</button>
</form>

<p class="muted" id="result-count"><?= $result['total'] ?> listing<?= $result['total'] === 1 ? '' : 's' ?></p>

<ul id="results" class="cards">
    <?php foreach ($result['items'] as $item): ?>
        <li class="card">
            <a href="/listings/<?= (int)$item['id'] ?>">
                <span class="price"><?= View::money((int)$item['price_cents']) ?></span>
                <strong><?= View::e($item['title']) ?></strong>
                <span class="meta"><?= View::e($item['category']) ?> &middot; <?= View::e($item['seller']) ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?php if ($result['pages'] > 1): ?>
<nav class="pager">
    <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
        <a class="<?= $p === $result['page'] ? 'current' : '' ?>"
           href="/?<?= View::e(http_build_query(['q' => $q, 'category' => $category, 'page' => $p])) ?>"><?= $p ?></a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
