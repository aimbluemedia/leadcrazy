<?php /** @var ?string $sent @var ?string $error @var string $email */ use App\Support\Csrf; use App\Support\View; ?>
<?= View::render('auth/_open', get_defined_vars()) ?>
  <div class="auth__card">
    <?= View::render('partials/logo') ?>
    <?php if ($sent): ?>
      <h1>Check your email</h1>
      <p class="muted">If <strong><?= View::e($sent) ?></strong> has a LeadCrazy account, we have sent it a link to choose a new password. It works for one hour.</p>
      <p class="muted" style="font-size:14px">Nothing there? Check your spam folder, or <a href="/members/forgot">try again</a>.</p>
      <a class="btn btn--primary btn--block" href="/members/login">Back to sign in</a>
    <?php else: ?>
      <h1>Forgot your password?</h1>
      <p class="muted" style="font-size:15px">Enter the email you sign in with and we will send you a link to choose a new one.</p>
      <?php if ($error): ?><div class="alert" role="alert" style="margin-bottom:14px"><?= View::e($error) ?></div><?php endif; ?>
      <form class="form" method="post" action="/members/forgot">
        <?= Csrf::field() ?>
        <div><label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="username" autofocus value="<?= View::e($email) ?>"></div>
        <button class="btn btn--primary btn--block" type="submit">Email me a reset link</button>
      </form>
      <p class="form__note" style="margin-top:16px"><a href="/members/login">Back to sign in</a></p>
    <?php endif; ?>
  </div>
<?= View::render('auth/_close') ?>
