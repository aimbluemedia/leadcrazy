<?php /** @var string $token @var bool $valid @var ?string $error @var int $min */ use App\Support\Csrf; use App\Support\View; ?>
<?= View::render('auth/_open', get_defined_vars()) ?>
  <div class="auth__card">
    <?= View::render('partials/logo') ?>
    <?php if (!$valid): ?>
      <h1>That link no longer works</h1>
      <p class="muted">Reset links last one hour and work once. Ask for a fresh one and use the newest email.</p>
      <a class="btn btn--primary btn--block" href="/members/forgot">Send me a new link</a>
    <?php else: ?>
      <h1>Choose a new password</h1>
      <?php if ($error): ?><div class="alert" role="alert" style="margin-bottom:14px"><?= View::e($error) ?></div><?php endif; ?>
      <form class="form" method="post" action="/members/reset">
        <?= Csrf::field() ?>
        <input type="hidden" name="token" value="<?= View::e($token) ?>">
        <div><label for="pw">New password</label><input id="pw" name="password" type="password" required minlength="<?= $min ?>" autocomplete="new-password" autofocus><p class="hint">At least <?= $min ?> characters.</p></div>
        <div><label for="pw2">Confirm new password</label><input id="pw2" name="password_confirm" type="password" required minlength="<?= $min ?>" autocomplete="new-password"></div>
        <button class="btn btn--primary btn--block" type="submit">Save and sign in</button>
      </form>
    <?php endif; ?>
  </div>
<?= View::render('auth/_close') ?>
