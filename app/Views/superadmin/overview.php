<?php
/** @var array<string,mixed> $stats @var array<string,mixed> $leadStats @var list<array<string,mixed>> $queue @var list<array<string,mixed>> $requested @var int $openRequests @var int $mrr */
use App\Support\Plans;
use App\Support\View;
?>
<div class="app__head"><h1>Overview</h1></div>
<div class="kpis">
  <div class="kpi"><strong><?= (int) ($stats['accounts'] ?? 0) ?></strong><span>Accounts &middot; <?= (int) ($stats['free'] ?? 0) ?> free / <?= (int) ($stats['pro'] ?? 0) ?> pro / <?= (int) ($stats['premium'] ?? 0) ?> premium</span></div>
  <div class="kpi"><strong>$<?= number_format($mrr) ?></strong><span>Monthly recurring (plan list price)</span></div>
  <div class="kpi"><strong><?= (int) ($stats['live'] ?? 0) ?></strong><span>Live pages</span></div>
  <div class="kpi"><strong><?= (int) ($leadStats['week'] ?? 0) ?></strong><span>Leads in the last 7 days (<?= (int) ($leadStats['total'] ?? 0) ?> total)</span></div>
</div>

<div class="panel">
  <h2>Build queue (<?= count($queue) ?>)</h2>
  <?php if ($queue === []): ?><p class="muted">Nothing waiting. Every page is built.</p><?php else: ?>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Business</th><th>Plan</th><th>Status</th><th>Intake sent</th><th>Signed up</th></tr></thead>
    <tbody><?php foreach ($queue as $a): ?>
      <tr><td><a href="/superadmin/account?id=<?= (int) $a['id'] ?>"><?= View::e($a['business_name']) ?></a></td>
          <td><span class="tag tag--<?= View::e($a['plan']) ?>"><?= View::e(Plans::name((string) $a['plan'])) ?></span><?= $a['requested_plan'] ? ' &rarr; ' . View::e(Plans::name((string) $a['requested_plan'])) : '' ?></td>
          <td><span class="tag tag--<?= View::e($a['page_status']) ?>"><?= View::e($a['page_status']) ?></span></td>
          <td><?= $a['intake_at'] ? View::e(date('M j, g:ia', strtotime((string) $a['intake_at']) ?: 0)) : '<span class="muted">not yet</span>' ?></td>
          <td><?= View::e(date('M j', strtotime((string) $a['created_at']) ?: 0)) ?></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
  <?php endif; ?>
</div>

<?php if ($requested !== []): ?>
<div class="panel">
  <h2>Plan requests</h2>
  <p class="muted">Members who chose a paid plan but have not paid by card (Stripe off, or checkout abandoned).</p>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Business</th><th>Now</th><th>Wants</th></tr></thead>
    <tbody><?php foreach ($requested as $a): ?>
      <tr><td><a href="/superadmin/account?id=<?= (int) $a['id'] ?>&tab=settings"><?= View::e($a['business_name']) ?></a></td>
          <td><?= View::e(Plans::name((string) $a['plan'])) ?></td><td><?= View::e(Plans::name((string) $a['requested_plan'])) ?></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
</div>
<?php endif; ?>

<?php if ($openRequests > 0): ?>
  <div class="panel panel--warn"><h2><?= $openRequests ?> open change request<?= $openRequests === 1 ? '' : 's' ?></h2><a class="btn btn--primary btn--sm" href="/superadmin/requests">Work them</a></div>
<?php endif; ?>
