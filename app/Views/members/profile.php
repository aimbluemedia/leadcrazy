<?php
/** @var array<string,mixed> $user @var string $editUrl */
use App\Support\View;
$rows = [
    'Name' => $user['name'] ?? '' ?: trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
    'Email' => $user['email'] ?? '',
    'Business' => $user['business_name'] ?? '',
    'Phone' => $user['phone'] ?? '',
];
?>
<div class="app__head"><h1>Profile</h1></div>
<div class="panel" style="max-width:640px">
  <p class="muted" style="margin-top:0">Your login and contact details are kept on Toutify, LeadCrazy's parent site, so they stay the same across every Toutify site you use.</p>
  <dl class="dl">
    <?php foreach ($rows as $label => $value): ?>
      <dt><?= $label ?></dt><dd><?= trim((string) $value) !== '' ? View::e((string) $value) : '<span class="muted">Not set</span>' ?></dd>
    <?php endforeach; ?>
  </dl>
  <p style="margin:20px 0 0"><a class="btn btn--primary" href="<?= View::e($editUrl) ?>">Edit on Toutify</a></p>
  <p class="hint">Change your name, email, business, phone or password there; it shows up here automatically.</p>
</div>
