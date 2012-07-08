<?php use CampusBoard\View; ?>
<article class="listing">
    <p class="muted"><a href="/">&larr; All listings</a> / <?= View::e($listing['category']) ?></p>
    <h1><?= View::e($listing['title']) ?></h1>
    <p class="price big"><?= View::money((int)$listing['price_cents']) ?></p>
    <?php if ($listing['status'] === 'sold'): ?><p class="badge">Sold</p><?php endif; ?>
    <div class="body"><?= nl2br(View::e($listing['body'])) ?></div>
    <p class="meta">Posted by <?= View::e($listing['seller']) ?> on <?= View::e(date('M j, Y', strtotime($listing['created_at']))) ?></p>
    <?php if ($user): ?>
        <a class="btn" href="mailto:<?= View::e($listing['seller_email']) ?>?subject=<?= rawurlencode('CampusBoard: ' . $listing['title']) ?>">Contact seller</a>
    <?php else: ?>
        <p><a href="/login?next=<?= urlencode('/listings/' . $listing['id']) ?>">Log in</a> to contact the seller.</p>
    <?php endif; ?>
</article>
