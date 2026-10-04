<?php
/** @var array<string,mixed> $account @var list<array<string,mixed>> $requests @var bool $hasIntake */
use App\Support\Csrf;
use App\Support\Pages;
use App\Support\Plans;
use App\Support\View;
$url = Pages::url((string) $account['slug']);
$plan = (string) $account['plan'];
?>
<div class="app__head"><h1>Your page</h1></div>
<?= View::render('members/_status', ['account' => $account, 'hasIntake' => $hasIntake]) ?>

<div class="panel">
  <h2>Your page address</h2>
  <p style="font-size:18px;word-break:break-all"><strong><?= View::e($url) ?></strong></p>
  <div class="row">
    <?php if ($account['page_status'] === 'live'): ?>
      <a class="btn btn--primary btn--sm" href="<?= View::e($url) ?>" target="_blank" rel="noopener">Open my page &#8599;</a>
    <?php endif; ?>
    <a class="btn btn--ghost btn--sm" href="/members/intake">Update business details</a>
  </div>
  <p class="hint" style="margin-top:10px">Share this link on Google Business, Facebook, Nextdoor, flyers and truck wraps.</p>
</div>

<div class="panel">
  <h2>What your plan shows</h2>
  <dl class="dl">
    <dt>Lead form</dt><dd>Yes</dd>
    <dt>Services &amp; cities</dt><dd>Yes</dd>
    <dt>Offers, gallery, reviews, zip codes</dt><dd><?= Plans::fullPage($plan) ? 'Yes' : 'No &mdash; <a href="/members/billing">upgrade to Pro</a>' ?></dd>
    <dt>MonsterList listing</dt><dd><?= Plans::onMonsterList($plan) ? 'Yes &mdash; listed while your page is live' : 'No &mdash; <a href="/members/billing">upgrade to Pro</a>' ?></dd>
    <dt>On your own website</dt><dd><?= Plans::canEmbed($plan) ? 'Yes &mdash; <a href="/members/embed">get the code</a>' : 'No &mdash; <a href="/members/billing">upgrade to Premium</a>' ?></dd>
  </dl>
</div>

<div class="panel">
  <h2>Ask for a change</h2>
  <p class="muted">New offer, new photos, different wording? Tell us and our team will update your page.</p>
  <form class="form" method="post" action="/members/page/request">
    <?= Csrf::field() ?>
    <textarea name="body" rows="4" required placeholder="E.g. Replace the spring offer with a summer special: $500 off any paver patio over 400 sq ft, ends August 31."></textarea>
    <div><button class="btn btn--primary" type="submit">Send request</button></div>
  </form>
  <?php if ($requests !== []): ?>
    <div class="table-wrap" style="margin-top:16px"><table class="table">
      <thead><tr><th>Sent</th><th>Request</th><th>Status</th></tr></thead>
      <tbody><?php foreach ($requests as $r): ?>
        <tr><td style="white-space:nowrap"><?= View::e(date('M j', strtotime((string) $r['created_at']) ?: 0)) ?></td>
            <td style="white-space:pre-wrap"><?= View::e($r['body']) ?></td>
            <td><span class="tag tag--<?= $r['status'] === 'done' ? 'won' : 'new' ?>"><?= $r['status'] === 'done' ? 'Done' : 'Open' ?></span></td></tr>
      <?php endforeach; ?></tbody>
    </table></div>
  <?php endif; ?>
</div>
