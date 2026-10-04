<?php /** @var string $area @var bool $forced @var ?string $error @var int $min */ use App\Support\Csrf; use App\Support\View; ?>
<?= View::render('auth/_open', get_defined_vars()) ?>
  <div class="auth__card">
    <?= View::render('partials/logo') ?>
    <h1>Choose a password</h1>
    <p class="muted" style="font-size:14px"><?= $forced ? 'You signed in with a temporary password. Choose your own to continue.' : 'Change the password you sign in with.' ?></p>
    <?php if ($error): ?><div class="alert" role="alert" style="margin-bottom:14px"><?= View::e($error) ?></div><?php endif; ?>
    <form class="form" method="post" action="/<?= $area ?>/password">
      <?= Csrf::field() ?>
      <?php if (!$forced): ?>
        <div><label for="current">Current password</label><input id="current" name="current_password" type="password" required autocomplete="current-password"></div>
      <?php endif; ?>
      <div><label for="pw">New password</label><input id="pw" name="password" type="password" required minlength="<?= $min ?>" autocomplete="new-password"><p class="hint">At least <?= $min ?> characters.</p></div>
      <div><label for="pw2">Confirm new password</label><input id="pw2" name="password_confirm" type="password" required minlength="<?= $min ?>" autocomplete="new-password"></div>
      <button class="btn btn--primary btn--block" type="submit">Save password</button>
    </form>
    <?php if (!$forced): ?><p class="form__note" style="margin-top:14px"><a href="/<?= $area ?>">Cancel</a></p><?php endif; ?>
  </div>
<?= View::render('auth/_close') ?>
