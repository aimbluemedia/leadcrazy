<?php
/**
 * Members dashboard: welcome banner, the three steps to a live, shared lead
 * page (until they are done), then the numbers and the latest leads.
 *
 * @var array<string,mixed> $account @var array<string,mixed> $user @var array<string,mixed> $counts
 * @var list<array<string,mixed>> $recent @var int $monthCount @var bool $hasIntake @var string $plan
 */
use App\Support\Pages;
use App\Support\Plans;
use App\Support\Toutify\Client;
use App\Support\View;

$cap = Plans::FREE_MONTHLY_LEADS;
$locked = (int) ($counts['locked'] ?? 0);
$total = (int) ($counts['total'] ?? 0);
$status = (string) $account['page_status'];
$live = $status === 'live';
$first = trim((string) ($user['first_name'] ?? '')) ?: (explode(' ', trim((string) ($user['name'] ?? '')))[0] ?? '');
$pageUrl = Pages::url((string) $account['slug']);
$icon = static fn (string $d): string => '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';

// The three steps. "done" decides the chip; the first step that is not done
// and that the member can act on gets "Do this next".
$steps = [
    [
        'title' => 'Business profile',
        'icon' => '<path d="M3 21h18M5 21V8l7-5 7 5v13"/><path d="M10 21v-6h4v6"/>',
        'done' => $hasIntake || $live,
        'text' => 'Tell us about your business &mdash; logo, photos, services and what makes you different &mdash; so your page sells for you.',
        'chips' => [],
        'action' => $hasIntake || $live ? ['Edit my profile', '/members/intake', false] : ['Complete my profile', '/members/intake', true],
        'waiting' => false,
    ],
    [
        'title' => 'Lead page approved',
        'icon' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="m9 12 2 2 4-4"/>',
        'done' => $live,
        'text' => $live ? 'Your Million Dollar Lead Form is live and taking requests.' : 'Our team reviews your page and publishes it, usually within one business day.',
        'chips' => $live ? [View::e(preg_replace('#^https?://#', '', $pageUrl))] : ['In review'],
        'action' => $live ? ['View my page', $pageUrl, false] : ['See my page details', '/members/page', false],
        'waiting' => !$live && ($hasIntake || $status === 'building'),
    ],
    [
        'title' => 'Share your page',
        'icon' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
        'done' => $total > 0,
        'text' => 'Put your link on Google, Facebook, texts and your website so customers can find it.',
        'chips' => $total > 0 ? [$total . ' lead' . ($total === 1 ? '' : 's') . ' so far'] : ['Google', 'Facebook', 'Website'],
        'action' => ['Get my link', '/members/embed', !$live ? false : $total === 0],
        'waiting' => false,
    ],
];
$doneCount = count(array_filter($steps, static fn ($s) => $s['done']));
$next = null;
foreach ($steps as $i => $s) {
    if (!$s['done'] && !$s['waiting'] && !($i === 2 && !$live)) {
        $next = $i;
        break;
    }
}
?>
<section class="welcome">
  <div class="welcome__copy">
    <p class="welcome__tag"><span aria-hidden="true"></span>Toutify member</p>
    <h1>Welcome back<?= $first !== '' ? ', ' . View::e($first) : '' ?></h1>
    <p class="welcome__sub"><?= View::e((string) $account['business_name']) ?> &middot; LeadCrazy is included with your Toutify membership.</p>
    <div class="welcome__chips">
      <span>Plan <b><?= View::e(Plans::name($plan)) ?> &middot; <?= View::e(Plans::priceLabel($plan) === 'Free' ? '$0/mo' : Plans::priceLabel($plan)) ?></b></span>
      <span>Leads this month <b><?= $monthCount ?><?= Plans::hasLeadCap($plan) ? ' of ' . $cap : '' ?></b></span>
      <span>Page <b><?= $live ? 'Live' : ($status === 'paused' ? 'Paused' : 'In review') ?></b></span>
    </div>
  </div>
  <div class="welcome__actions">
    <?php if (Plans::isPaid($plan) && $live): ?>
      <a class="welcome__btn" href="<?= View::e($pageUrl) ?>" target="_blank" rel="noopener">View my page &#8599;</a>
    <?php else: ?>
      <a class="welcome__btn" href="/members/billing">Upgrade my plan &rarr;</a>
    <?php endif; ?>
    <a class="welcome__btn welcome__btn--ghost" href="<?= View::e(Client::issuer() !== '' ? Client::issuer() . '/members/' : 'https://toutify.com/members/') ?>" target="_blank" rel="noopener">Toutify dashboard &#8599;</a>
  </div>
</section>

<?php if ($status === 'paused'): ?>
  <div class="panel panel--warn"><h2>Your page is paused</h2><p>It is not visible to the public. Contact us to turn it back on.</p></div>
<?php endif; ?>

<?php if ($doneCount < 3): ?>
<section class="steps-start">
  <span class="steps-start__flag">Start here first</span>
  <div class="steps-start__head">
    <div>
      <h2>Finish these 3 steps to start getting leads</h2>
      <p>Complete your profile, get your page approved, then share it everywhere your customers look.</p>
    </div>
    <div class="steps-start__progress">
      <strong><?= $doneCount ?> of 3 done</strong>
      <div class="meter"><i style="width:<?= (int) round($doneCount / 3 * 100) ?>%"></i></div>
    </div>
  </div>
  <div class="step-cards">
    <?php foreach ($steps as $i => $s): ?>
      <article class="step-card<?= $next === $i ? ' is-next' : '' ?><?= $s['done'] ? ' is-done' : '' ?>">
        <div class="step-card__top">
          <span class="step-card__icon"><?= $icon($s['icon']) ?></span>
          <?php if ($s['done']): ?>
            <span class="step-chip step-chip--done">&#10003; Done</span>
          <?php elseif ($next === $i): ?>
            <span class="step-chip step-chip--next">Do this next</span>
          <?php elseif ($s['waiting']): ?>
            <span class="step-chip step-chip--wait">In review</span>
          <?php endif; ?>
        </div>
        <p class="step-card__n">Step <?= $i + 1 ?></p>
        <h3><?= $s['title'] ?></h3>
        <p class="step-card__text"><?= $s['text'] ?></p>
        <?php if ($s['chips'] !== [] && !($s['waiting'] && $s['chips'] === ['In review'])): ?>
          <div class="step-card__chips"><?php foreach ($s['chips'] as $chip): ?><span><?= $chip ?></span><?php endforeach; ?></div>
        <?php endif; ?>
        <?php [$label, $href, $primary] = $s['action']; ?>
        <a class="step-card__btn<?= $primary || $next === $i ? ' step-card__btn--primary' : '' ?>" href="<?= View::e($href) ?>"<?= str_starts_with($href, 'http') ? ' target="_blank" rel="noopener"' : '' ?>>
          <?= $s['done'] ? '&#9998; ' : '' ?><?= $label ?><?= $primary || $next === $i ? ' &rarr;' : '' ?>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<div class="kpis">
  <div class="kpi"><strong><?= (int) ($counts['fresh'] ?? 0) ?></strong><span>New leads to call</span></div>
  <div class="kpi"><strong><?= $monthCount ?></strong><span>Leads this month</span>
    <?php if (Plans::hasLeadCap($plan)): ?><div class="meter" title="<?= $monthCount ?> of <?= $cap ?>"><i style="width:<?= min(100, (int) round($monthCount / $cap * 100)) ?>%"></i></div><?php endif; ?></div>
  <div class="kpi"><strong><?= $total ?></strong><span>Leads all time</span></div>
  <div class="kpi"><strong><?= View::e(Plans::name($plan)) ?></strong><span><?= Plans::isPaid($plan) ? View::e(Plans::priceLabel($plan)) . ' plan' : '$0/mo plan' ?></span></div>
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
