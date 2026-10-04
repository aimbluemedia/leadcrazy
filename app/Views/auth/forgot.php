<?php /** @var string $support */ use App\Support\View; ?>
<?= View::render('auth/_open', get_defined_vars()) ?>
  <div class="auth__card">
    <?= View::render('partials/logo') ?>
    <h1>Forgot your password?</h1>
    <p>No problem. Email us from the address on your account and we will send you a temporary password. You will choose a new one when you sign in.</p>
    <?php if ($support !== ''): ?>
      <p><a class="btn btn--primary btn--block" href="mailto:<?= View::e($support) ?>?subject=<?= rawurlencode('Password reset') ?>"><?= View::e($support) ?></a></p>
    <?php endif; ?>
    <p class="form__note"><a href="/members/login">Back to sign in</a></p>
  </div>
<?= View::render('auth/_close') ?>
