<?php /** @var list<array<string,mixed>> $leads */ use App\Support\View; ?>
<div class="app__head"><h1>All leads</h1><span class="muted">Latest 300. Contact details stay in each member's dashboard.</span></div>
<div class="panel"><div class="table-wrap"><table class="table">
  <thead><tr><th>Received</th><th>Business</th><th>Lead</th><th>City / Zip</th><th>Source</th><th>Status</th></tr></thead>
  <tbody><?php foreach ($leads as $l): ?>
    <tr><td style="white-space:nowrap"><?= View::e(date('M j, g:ia', strtotime((string) $l['created_at']) ?: 0)) ?></td>
        <td><a href="/superadmin/account?id=<?= (int) $l['account_id'] ?>"><?= View::e($l['business_name']) ?></a></td>
        <td><?= View::e($l['first_name'] . ' ' . $l['last_name']) ?> <?= (int) $l['is_locked'] && $l['plan'] === 'free' ? '<span class="tag tag--locked">over cap</span>' : '' ?></td>
        <td><?= View::e(trim($l['city'] . ' ' . $l['zip'])) ?></td>
        <td><?= View::e($l['source']) ?></td>
        <td><span class="tag tag--<?= View::e($l['status']) ?>"><?= View::e($l['status']) ?></span></td></tr>
  <?php endforeach; ?>
  <?php if ($leads === []): ?><tr><td colspan="6" class="muted">No leads yet.</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
