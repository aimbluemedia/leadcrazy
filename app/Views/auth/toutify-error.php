<?php /** @var string $message */ use App\Support\View; ?>
<?= View::render('auth/_open', get_defined_vars()) ?>
  <div class="auth__card">
    <?= View::render('partials/logo') ?>
    <h1>We could not sign you in</h1>
    <div class="alert" role="alert" style="margin:14px 0"><?= View::e($message) ?></div>
    <a class="btn btn--primary btn--block" href="/auth/toutify/login">Try again</a>
    <p class="form__note center" style="margin-top:14px"><a href="/">Back to LeadCrazy</a></p>
  </div>
<?= View::render('auth/_close') ?>
