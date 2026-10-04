<?php
/** @var array<string,mixed> $account @var bool $stripeLive @var bool $testMode @var bool $subscribed */
use App\Support\Csrf;
use App\Support\Plans;
use App\Support\View;
$current = (string) $account['plan'];
$requested = (string) ($account['requested_plan'] ?? '');
?>
<div class="app__head"><h1>Plan &amp; billing</h1>
  <?php if ($subscribed || !empty($account['stripe_customer_id'])): ?>
    <form method="post" action="/members/billing/manage"><?= Csrf::field() ?><button class="btn btn--ghost btn--sm" type="submit">Manage billing &amp; invoices</button></form>
  <?php endif; ?>
</div>

<?php if ($stripeLive && $testMode): ?><div class="alert alert--info" style="margin-bottom:18px">Stripe is in test mode &mdash; no real cards are charged.</div><?php endif; ?>
<?php if ($subscribed && !empty($account['plan_renews_at'])): ?>
  <p class="muted">Your <?= View::e(Plans::name($current)) ?> plan renews on <?= View::e(date('F j, Y', strtotime((string) $account['plan_renews_at']) ?: 0)) ?>.
  <?= ($account['stripe_status'] ?? '') === 'past_due' ? '<strong style="color:var(--red)">Your last payment failed &mdash; update your card under "Manage billing".</strong>' : '' ?></p>
<?php endif; ?>
<?php if ($requested !== '' && $requested !== $current && !$stripeLive): ?>
  <div class="alert alert--info" style="margin-bottom:18px">You asked for <?= View::e(Plans::name($requested)) ?>. We will be in touch to set it up.</div>
<?php endif; ?>

<div class="plans" style="margin-top:18px">
  <?php foreach (Plans::ALL as $plan): $d = Plans::DETAILS[$plan]; $isCurrent = $plan === $current; ?>
    <div class="plan<?= $isCurrent ? ' plan--current' : ($plan === Plans::PRO ? ' plan--featured' : '') ?>">
      <?php if ($isCurrent): ?><span class="plan__tag" style="background:var(--green)">Your plan</span><?php endif; ?>
      <p class="plan__name"><?= View::e($d['name']) ?></p>
      <p class="plan__price">$<?= (int) $d['price'] ?><small><?= $d['price'] > 0 ? '/month' : '' ?></small></p>
      <p class="plan__tagline"><?= View::e($d['tagline']) ?></p>
      <ul><?php foreach ($d['features'] as $f): ?><li><?= View::e($f) ?></li><?php endforeach; ?></ul>
      <?php if ($isCurrent): ?>
        <button class="btn btn--ghost btn--block" disabled>Current plan</button>
      <?php elseif ($plan === Plans::FREE): ?>
        <?php if ($subscribed): ?>
          <form method="post" action="/members/billing/manage"><?= Csrf::field() ?><button class="btn btn--ghost btn--block" type="submit">Cancel in billing portal</button></form>
        <?php else: ?>
          <form method="post" action="/members/plan" data-confirm="Move to the Free plan? Offers, gallery and reviews will be hidden."><?= Csrf::field() ?><input type="hidden" name="plan" value="free"><button class="btn btn--ghost btn--block" type="submit">Switch to Free</button></form>
        <?php endif; ?>
      <?php elseif ($subscribed): ?>
        <form method="post" action="/members/billing/manage"><?= Csrf::field() ?><button class="btn btn--primary btn--block" type="submit">Switch to <?= View::e($d['name']) ?></button></form>
      <?php elseif ($stripeLive): ?>
        <form method="post" action="/members/billing/start"><?= Csrf::field() ?><input type="hidden" name="plan" value="<?= $plan ?>"><button class="btn btn--primary btn--block" type="submit">Upgrade &mdash; <?= View::e(Plans::priceLabel($plan)) ?></button></form>
      <?php else: ?>
        <form method="post" action="/members/plan"><?= Csrf::field() ?><input type="hidden" name="plan" value="<?= $plan ?>"><button class="btn btn--primary btn--block" type="submit"<?= $requested === $plan ? ' disabled' : '' ?>><?= $requested === $plan ? 'Requested' : 'Request this plan' ?></button></form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<p class="hint" style="margin-top:16px">Payments are processed securely by Stripe. Cancel any time; your plan runs to the end of the period you paid for.</p>
