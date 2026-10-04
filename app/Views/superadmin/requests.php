<?php /** @var list<array<string,mixed>> $requests */ use App\Support\Csrf; use App\Support\View; ?>
<div class="app__head"><h1>Change requests</h1></div>
<div class="panel"><div class="table-wrap"><table class="table">
  <thead><tr><th>Sent</th><th>Business</th><th>Request</th><th></th></tr></thead>
  <tbody><?php foreach ($requests as $r): ?>
    <tr><td style="white-space:nowrap"><?= View::e(date('M j, g:ia', strtotime((string) $r['created_at']) ?: 0)) ?></td>
        <td><a href="/superadmin/account?id=<?= (int) $r['account_id'] ?>"><?= View::e($r['business_name']) ?></a></td>
        <td style="white-space:pre-wrap;max-width:520px"><?= View::e($r['body']) ?></td>
        <td><?php if ($r['status'] === 'open'): ?>
          <form method="post" action="/superadmin/requests/done"><?= Csrf::field() ?><input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>"><button class="btn btn--ghost btn--sm" type="submit">Mark done</button></form>
        <?php else: ?><span class="tag tag--won">done</span><?php endif; ?></td></tr>
  <?php endforeach; ?>
  <?php if ($requests === []): ?><tr><td colspan="4" class="muted">No requests yet.</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
