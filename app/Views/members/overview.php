<?php
/** @var array<string,mixed> $account @var array<string,mixed> $counts @var list<array<string,mixed>> $recent @var int $monthCount @var bool $hasIntake @var string $plan */
use App\Support\Leads;
use App\Support\Pages;
use App\Support\Plans;
use App\Support\View;
$cap = Plans::FREE_MONTHLY_LEADS;
$locked = (int) ($counts['locked'] ?? 0);
?>
<div class="app__head">
  <h1>Dashboard</h1>
  <?php if ($account['page_status'] === 'live'): ?>
    <a class="btn btn--primary btn--sm" href="<?= View::e(Pages::url((string) $account['slug'])) ?>" target="_blank" rel="noopener">View my page &#8599;</a>
  <?php endif; ?>
</div>

<?= View::render('members/_status', ['account' => $account, 'hasIntake' => $hasIntake]) ?>

<div class="kpis">
  <div class="kpi"><strong><?= (int) ($counts['fresh'] ?? 0) ?></strong><span>New leads to call</span></div>
  <div class="kpi"><strong><?= $monthCount ?></strong><span>Leads this month</span>
    <?php if (Plans::hasLeadCap($plan)): ?><div class="meter" title="<?= $monthCount ?> of <?= $cap ?>"><i style="width:<?= min(100, (int) round($monthCount / $cap * 100)) ?>%"></i></div><?php endif; ?></div>
  <div class="kpi"><strong><?= (int) ($counts['total'] ?? 0) ?></strong><span>Leads all time</span></div>
  <div class="kpi"><strong><?= View::e(Plans::name($plan)) ?></strong><span><?= View::e(Plans::priceLabel($plan)) ?></span></div>
</div>

<?php if (Plans::hasLeadCap($plan) && ($locked > 0 || $monthCount >= $cap)): ?>
  <div class="panel panel--warn">
    <h2><?= $locked > 0 ? $locked . ' lead' . ($locked === 1 ? ' is' : 's are') . ' waiting for you' : 'You have reached ' . $cap . ' leads this month' ?></h2>
    <p>The Free plan shows <?= $cap ?> leads a month. Every extra lead is saved &mdash; upgrade to Pro to see them all, plus get offers, a photo gallery and a MonsterList listing.</p>
    <a class="btn btn--primary" href="/members/billing">Upgrade for $19/month</a>
  </div>
<?php endif; ?>

<div class="panel">
  <div class="row" style="justify-content:space-between"><h2 style="margin:0">Latest leads</h2><a href="/members/leads">See all &rarr;</a></div>
  <?php if ($recent === []): ?>
    <p class="muted" style="margin-top:12px">No leads yet. They will appear here the moment someone fills in your form.</p>
  <?php else: ?>
    <?= View::render('members/_leads-table', ['leads' => $recent, 'account' => $account]) ?>
  <?php endif; ?>
</div>
