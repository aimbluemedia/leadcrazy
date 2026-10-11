<?php /** @var string $email @var string $business */ use App\Support\View; ?>
<?= View::render('auth/_open', get_defined_vars()) ?>
  <div class="auth__card pending">
    <?= View::render('partials/logo') ?>
    <span class="pending__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
    </span>
    <h1>You already have a LeadCrazy profile</h1>
    <p class="lede" style="font-size:17px">We found an existing profile that matches your email, business name or website<?= $business !== '' ? ' for <strong>' . View::e($business) . '</strong>' : '' ?>. Log in to view it and pick up where you left off.</p>
    <a class="btn btn--primary btn--xl btn--block" href="/members/login<?= $email !== '' ? '?email=' . View::e(rawurlencode($email)) : '' ?>">Log in and view my profile</a>
    <p class="form__note center" style="margin-top:14px"><a href="/members/forgot">Forgot your password?</a></p>
    <p class="form__note center">Not your business? <a href="mailto:<?= View::e((string) App\Support\Config::get('support_email', '')) ?>">Contact us</a> and we will sort it out.</p>
  </div>
<?= View::render('auth/_close') ?>
