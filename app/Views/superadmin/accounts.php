<?php
/** @var list<array<string,mixed>> $accounts @var string $q */
use App\Support\Plans;
use App\Support\View;
?>
<div class="app__head"><h1>Accounts &amp; pages</h1>
  <form method="get" action="/superadmin/accounts" class="row"><input class="field" style="width:240px" type="search" name="q" value="<?= View::e($q) ?>" placeholder="Name, slug or email"><button class="btn btn--ghost btn--sm" type="submit">Search</button></form>
</div>
<div class="panel"><div class="table-wrap"><table class="table">
  <thead><tr><th>Business</th><th>Page</th><th>Plan</th><th>Status</th><th>Leads</th><th>Owner</th><th>Since</th></tr></thead>
  <tbody><?php foreach ($accounts as $a): ?>
    <tr><td><a href="/superadmin/account?id=<?= (int) $a['id'] ?>"><?= View::e($a['business_name']) ?></a></td>
        <td><a href="/<?= View::e($a['slug']) ?>" target="_blank" rel="noopener" style="font-weight:400">/<?= View::e($a['slug']) ?></a></td>
        <td><span class="tag tag--<?= View::e($a['plan']) ?>"><?= View::e(Plans::name((string) $a['plan'])) ?></span></td>
        <td><span class="tag tag--<?= View::e($a['page_status']) ?>"><?= View::e($a['page_status']) ?></span></td>
        <td><?= (int) $a['lead_count'] ?></td>
        <td><?= View::e($a['email']) ?></td>
        <td><?= View::e(date('M j, Y', strtotime((string) $a['created_at']) ?: 0)) ?></td></tr>
  <?php endforeach; ?>
  <?php if ($accounts === []): ?><tr><td colspan="7" class="muted">No accounts<?= $q !== '' ? ' match' : ' yet' ?>.</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
