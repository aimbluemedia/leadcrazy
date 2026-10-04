<?php /** @var bool $hasAdmin @var ?string $error @var ?list<string> $log */ use App\Support\View; ?>
<section class="section"><div class="container" style="max-width:640px">
  <h1>Install LeadCrazy</h1>
  <p>Creates the database tables from <code>database/schema.sql</code><?= $hasAdmin ? '' : ' and your superadmin login' ?>. Safe to run more than once.</p>
  <?php if (!empty($error)): ?><div class="alert" style="margin-bottom:14px"><?= View::e($error) ?></div><?php endif; ?>
  <?php if (!empty($log)): ?>
    <pre class="card" style="white-space:pre-wrap;margin-bottom:18px"><?= View::e(implode("\n", $log)) ?></pre>
    <?php if ($hasAdmin): ?><p><a class="btn btn--primary" href="/superadmin/login">Go to superadmin sign in</a></p><?php endif; ?>
  <?php endif; ?>
  <form class="form card" method="post" action="/install">
    <div><label for="k">app_key (from app/config.php)</label><input id="k" name="app_key" type="password" required autocomplete="off"></div>
    <?php if (!$hasAdmin): ?>
      <div><label for="e">Superadmin email</label><input id="e" name="email" type="email"></div>
      <div><label for="p">Superadmin password</label><input id="p" name="password" type="password" minlength="10" autocomplete="new-password"><p class="hint">At least 10 characters.</p></div>
    <?php endif; ?>
    <button class="btn btn--primary" type="submit">Create tables<?= $hasAdmin ? '' : ' &amp; superadmin' ?></button>
  </form>
</div></section>
