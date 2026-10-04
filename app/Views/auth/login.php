<?php /** @var string $area @var ?string $error @var ?string $notice */ use App\Support\Csrf; use App\Support\View; ?>
<?= View::render('auth/_open', get_defined_vars()) ?>
  <div class="auth__card">
    <?= View::render('partials/logo') ?>
    <h1><?= $area === 'superadmin' ? 'Superadmin' : 'Sign in' ?></h1>
    <p class="muted" style="font-size:14px"><?= $area === 'superadmin' ? 'LeadCrazy staff only.' : 'See your leads and manage your page.' ?></p>
    <?php if ($error): ?><div class="alert" role="alert" style="margin-bottom:14px"><?= View::e($error) ?></div><?php endif; ?>
    <?php if ($notice): ?><div class="alert alert--ok" style="margin-bottom:14px"><?= View::e($notice) ?></div><?php endif; ?>
    <form class="form" method="post" action="/<?= $area ?>/login">
      <?= Csrf::field() ?>
      <div><label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="username" autofocus></div>
      <div><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div>
      <button class="btn btn--primary btn--block" type="submit">Sign in</button>
    </form>
    <?php if ($area === 'members'): ?>
      <p class="form__note" style="margin-top:16px"><a href="/members/forgot">Forgot your password?</a></p>
      <p class="form__note">No account yet? <a href="/members/signup">Get your free page</a>.</p>
    <?php endif; ?>
  </div>
<?= View::render('auth/_close') ?>
