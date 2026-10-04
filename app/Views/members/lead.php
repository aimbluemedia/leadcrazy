<?php
/** @var array<string,mixed> $lead @var bool $visible */
use App\Support\Csrf;
use App\Support\Leads;
use App\Support\View;
$services = Leads::services($lead['services']);
?>
<div class="app__head">
  <h1><?= View::e($lead['first_name'] . ($visible ? ' ' . $lead['last_name'] : '')) ?></h1>
  <a class="btn btn--ghost btn--sm" href="/members/leads">&larr; All leads</a>
</div>

<?php if (!$visible): ?>
  <div class="panel panel--warn">
    <h2>This lead is locked</h2>
    <p>It arrived after your Free plan's 10 leads for the month. Upgrade to see how to reach <?= View::e($lead['first_name']) ?> &mdash; and every lead after.</p>
    <a class="btn btn--primary" href="/members/billing">Upgrade for $19/month</a>
  </div>
<?php endif; ?>

<div class="grid-2" style="align-items:start">
  <div class="panel">
    <h2>Contact</h2>
    <dl class="dl">
      <dt>Phone</dt><dd class="<?= $visible ? '' : 'locked' ?>"><?= $visible ? '<a href="tel:' . View::e(preg_replace('/[^0-9+]/', '', (string) $lead['phone'])) . '">' . View::e($lead['phone']) . '</a>' : '(555) 000-0000' ?></dd>
      <dt>Email</dt><dd class="<?= $visible ? '' : 'locked' ?>"><?= $visible ? '<a href="mailto:' . View::e($lead['email']) . '">' . View::e($lead['email']) . '</a>' : 'hidden@example.com' ?></dd>
      <dt>City / Zip</dt><dd><?= View::e(trim($lead['city'] . ' ' . $lead['zip'])) ?></dd>
      <dt>Received</dt><dd><?= View::e(date('l, F j, Y g:ia', strtotime((string) $lead['created_at']) ?: 0)) ?></dd>
      <dt>Came from</dt><dd><?= $lead['source'] === 'embed' ? 'Your website (embed)' : 'Your LeadCrazy page' ?></dd>
    </dl>
  </div>
  <div class="panel">
    <h2>Project</h2>
    <dl class="dl">
      <dt>Services</dt><dd><?= View::e($services ? implode(', ', $services) : '-') ?></dd>
      <dt>Start</dt><dd><?= View::e($lead['timeline'] ?? '-') ?></dd>
      <dt>Budget</dt><dd><?= View::e($lead['budget'] ?? '-') ?></dd>
      <dt>Offer</dt><dd><?= View::e($lead['offer'] ?? '-') ?></dd>
    </dl>
    <p style="margin-top:12px;white-space:pre-wrap" class="<?= $visible ? '' : 'locked' ?>"><?= $visible ? View::e($lead['message']) : 'Upgrade to read the project details.' ?></p>
  </div>
</div>

<?php if ($visible): ?>
<div class="panel">
  <h2>Track this lead</h2>
  <form class="form" method="post" action="/members/lead">
    <?= Csrf::field() ?>
    <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
    <div style="max-width:260px"><label for="status">Status</label>
      <select id="status" name="status">
        <?php foreach (Leads::STATUSES as $s): ?><option value="<?= $s ?>"<?= $lead['status'] === $s ? ' selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
      </select></div>
    <div><label for="notes">Notes (only you see these)</label><textarea id="notes" name="notes" rows="4"><?= View::e($lead['notes']) ?></textarea></div>
    <div><button class="btn btn--primary" type="submit">Save</button></div>
  </form>
</div>
<?php endif; ?>
