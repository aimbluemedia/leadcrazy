<?php
/** @var string $plan @var array<string,mixed> $old @var ?string $error */
use App\Controllers\PasswordController;
use App\Support\Csrf;
use App\Support\Plans;
use App\Support\View;
$o = static fn (string $k): string => View::e(is_string($old[$k] ?? null) ? $old[$k] : '');
$plan = is_string($old['plan'] ?? null) && Plans::isValid($old['plan']) ? $old['plan'] : $plan;
?>
<?= View::render('auth/_open', get_defined_vars()) ?>
  <div class="auth__card auth__card--wide">
    <?= View::render('partials/logo') ?>
    <h1>Get your Million Dollar Lead Form</h1>
    <p class="muted" style="font-size:14px">Create your account, tell us about your business, and our team builds your page.</p>
    <?php if ($error): ?><div class="alert" role="alert" style="margin-bottom:14px"><?= View::e($error) ?></div><?php endif; ?>
    <form class="form" method="post" action="/members/signup">
      <?= Csrf::field() ?>
      <fieldset style="border:0;padding:0;margin:0">
        <legend class="label">Choose your plan</legend>
        <div class="plan-pick">
          <?php foreach (Plans::ALL as $p): ?>
            <label><input type="radio" name="plan" value="<?= $p ?>"<?= $p === $plan ? ' checked' : '' ?>>
              <strong><?= View::e(Plans::name($p)) ?> &middot; <?= View::e(Plans::priceLabel($p)) ?></strong>
              <span><?= View::e(Plans::DETAILS[$p]['tagline']) ?></span></label>
          <?php endforeach; ?>
        </div>
        <p class="hint">Paid plans are billed monthly after you tell us about your business. Change or cancel any time.</p>
      </fieldset>
      <div><label for="business_name">Business name</label><input id="business_name" name="business_name" type="text" required maxlength="160" value="<?= $o('business_name') ?>" placeholder="Storm Landscaping"></div>
      <div class="form__row">
        <div><label for="first_name">First name</label><input id="first_name" name="first_name" type="text" required autocomplete="given-name" value="<?= $o('first_name') ?>"></div>
        <div><label for="last_name">Last name</label><input id="last_name" name="last_name" type="text" autocomplete="family-name" value="<?= $o('last_name') ?>"></div>
      </div>
      <div class="form__row">
        <div><label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="email" value="<?= $o('email') ?>"></div>
        <div><label for="phone">Business phone</label><input id="phone" name="phone" type="tel" required autocomplete="tel" value="<?= $o('phone') ?>"></div>
      </div>
      <div><label for="password">Password</label><input id="password" name="password" type="password" required minlength="<?= PasswordController::MIN_LENGTH ?>" autocomplete="new-password"><p class="hint">At least <?= PasswordController::MIN_LENGTH ?> characters.</p></div>
      <label class="check"><input type="checkbox" name="agree" value="1" required> <span>I agree to the <a href="/terms" target="_blank">terms</a> and <a href="/privacy" target="_blank">privacy policy</a>.</span></label>
      <button class="btn btn--primary btn--block" type="submit">Create my account &rarr;</button>
    </form>
    <p class="form__note" style="margin-top:14px">Already have an account? <a href="/members/login">Sign in</a>.</p>
  </div>
<?= View::render('auth/_close') ?>
