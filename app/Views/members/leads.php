<?php
/** @var list<array<string,mixed>> $leads @var string $status @var int $page @var int $pages @var int $total @var array<string,mixed> $account */
use App\Support\Leads;
use App\Support\View;
?>
<div class="app__head">
  <h1>Leads</h1>
  <a class="btn btn--ghost btn--sm" href="/members/leads/export">Export CSV</a>
</div>
<div class="tabs">
  <a href="/members/leads" <?= $status === '' ? 'aria-current="page"' : '' ?>>All</a>
  <?php foreach (Leads::STATUSES as $s): ?>
    <a href="/members/leads?status=<?= $s ?>" <?= $status === $s ? 'aria-current="page"' : '' ?>><?= ucfirst($s) ?></a>
  <?php endforeach; ?>
</div>
<div class="panel">
  <?php if ($leads === []): ?>
    <p class="muted">No leads<?= $status !== '' ? ' marked ' . View::e($status) : ' yet' ?>.</p>
  <?php else: ?>
    <?= View::render('members/_leads-table', ['leads' => $leads, 'account' => $account]) ?>
    <?php if ($pages > 1): ?>
      <div class="pager">
        <?php if ($page > 1): ?><a class="btn btn--ghost btn--sm" href="?status=<?= View::e($status) ?>&p=<?= $page - 1 ?>">&larr; Newer</a><?php endif; ?>
        <span class="muted">Page <?= $page ?> of <?= $pages ?> &middot; <?= $total ?> leads</span>
        <?php if ($page < $pages): ?><a class="btn btn--ghost btn--sm" href="?status=<?= View::e($status) ?>&p=<?= $page + 1 ?>">Older &rarr;</a><?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
