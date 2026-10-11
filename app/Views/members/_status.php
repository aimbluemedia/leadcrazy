<?php
/** Page status banner. @var array<string,mixed> $account @var bool $hasIntake */
use App\Support\Pages;
use App\Support\View;
$s = (string) $account['page_status'];
?>
<?php if ($s === 'intake' && !$hasIntake): ?>
  <div class="panel panel--warn"><h2>Step 1: tell us about your business</h2>
    <p>We build your page from your answers. It takes about five minutes.</p>
    <a class="btn btn--primary" href="/members/intake">Start the intake form &rarr;</a></div>
<?php elseif ($s === 'building' && !$hasIntake): ?>
  <div class="panel panel--warn"><h2>Your lead form is pending approval &mdash; finish your account</h2>
    <p>We have your lead form. Add your logo, project photos and a few words about your business, and we will review and publish your page at
      <strong><?= View::e(Pages::url((string) $account['slug'])) ?></strong>, usually within one business day.</p>
    <a class="btn btn--primary" href="/members/intake">Finish my account &rarr;</a></div>
<?php elseif ($s === 'intake' || $s === 'building'): ?>
  <div class="panel panel--warn"><h2>Your page is pending approval</h2>
    <p>Thanks for the details. Your page will be live at <strong><?= View::e(Pages::url((string) $account['slug'])) ?></strong> once our team approves it &mdash; usually within one business day.</p>
    <a class="btn btn--ghost btn--sm" href="/members/intake">Add or change details</a></div>
<?php elseif ($s === 'paused'): ?>
  <div class="panel panel--warn"><h2>Your page is paused</h2><p>It is not visible to the public. Contact us to turn it back on.</p></div>
<?php endif; ?>
