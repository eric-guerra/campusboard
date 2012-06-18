<?php use CampusBoard\Csrf; use CampusBoard\View; ?>
<h1>My listings</h1>
<?php if (!$items): ?>
    <p>You haven't posted anything yet. <a href="/listings/new">Post your first listing.</a></p>
<?php else: ?>
<table class="table">
    <thead><tr><th>Title</th><th>Price</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><a href="/listings/<?= (int)$item['id'] ?>"><?= View::e($item['title']) ?></a></td>
            <td><?= View::money((int)$item['price_cents']) ?></td>
            <td><?= View::e($item['status']) ?></td>
            <td>
                <form method="post" action="/listings/<?= (int)$item['id'] ?>/status" class="inline">
                    <?= Csrf::field() ?>
                    <?php if ($item['status'] === 'active'): ?>
                        <button name="status" value="sold">Mark sold</button>
                    <?php else: ?>
                        <button name="status" value="active">Relist</button>
                    <?php endif; ?>
                    <button name="status" value="removed" class="danger" data-confirm="Remove this listing?">Remove</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
