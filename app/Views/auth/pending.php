<?php /** @var string $business @var string $url @var string $email */ use App\Support\View; ?>
<?= View::render('auth/_open', get_defined_vars()) ?>
  <div class="auth__card auth__card--wide pending">
    <?= View::render('partials/logo') ?>
    <span class="pending__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
    </span>
    <h1>Your lead form is pending approval</h1>
    <p class="lede" style="font-size:17px">Thanks! We have received <strong><?= View::e($business) ?></strong>. Our team reviews every new page, usually within one business day. Once it is approved it goes live at:</p>
    <p class="slug-preview"><?= View::e($url) ?></p>

    <ol class="pending__steps">
      <li class="is-done"><span>&#10003;</span><div><strong>Lead form submitted</strong><p>Your services, service area and offer are saved.</p></div></li>
      <li class="is-next"><span>2</span><div><strong>Log in and finish your account</strong><p>Add your logo, project photos and a few words about your business so your page looks its best.</p></div></li>
      <li><span>3</span><div><strong>We approve and publish</strong><p>Your page goes live and leads start landing in your dashboard.</p></div></li>
    </ol>

    <a class="btn btn--primary btn--xl btn--block" href="/members/login?email=<?= View::e(rawurlencode($email)) ?>">Log in to finish your account</a>
    <p class="form__note center" style="margin-top:14px">Log in with <strong><?= View::e($email) ?></strong> and the password you just chose.</p>
  </div>
<?= View::render('auth/_close') ?>
