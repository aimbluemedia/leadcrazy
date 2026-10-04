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
<?php elseif ($s === 'intake' || $s === 'building'): ?>
  <div class="panel panel--warn"><h2>We are building your page</h2>
    <p>Thanks for the details. Your page will be at <strong><?= View::e(Pages::url((string) $account['slug'])) ?></strong> &mdash; usually ready within one business day.</p>
    <a class="btn btn--ghost btn--sm" href="/members/intake">Add or change details</a></div>
<?php elseif ($s === 'paused'): ?>
  <div class="panel panel--warn"><h2>Your page is paused</h2><p>It is not visible to the public. Contact us to turn it back on.</p></div>
<?php endif; ?>
