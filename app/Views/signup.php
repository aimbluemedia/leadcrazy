<?php
/**
 * /signup: Toutify's embedded sign-up form. Toutify holds every member's
 * login; the form only works on the addresses registered for LeadCrazy there.
 *
 * @var string $plan
 */
use App\Support\Plans;
use App\Support\View;
?>
<section class="section signup-page">
  <div class="container signup-page__grid">
    <div class="signup-page__copy">
      <span class="eyebrow">Free to start &middot; no credit card</span>
      <h1>Get your free <span class="accent">lead page.</span></h1>
      <p class="lede">Create your account in a minute. Then build your Million Dollar Lead Form and start turning visitors into customers.</p>
      <?php if ($plan !== Plans::FREE): ?>
        <p class="signup-page__plan">You picked <strong><?= View::e(Plans::name($plan)) ?></strong> (<?= View::e(Plans::priceLabel($plan)) ?>). You can start on Free and upgrade from your dashboard.</p>
      <?php endif; ?>
      <ul class="signup-page__points">
        <li>A lead page built around your services, area and best offer</li>
        <li>Every request in your dashboard and your inbox</li>
        <li>One login for LeadCrazy and the Toutify family of sites</li>
      </ul>
      <p class="muted">Already have an account? <a href="/auth/toutify/login">Log in</a>.</p>
    </div>
    <div class="signup-page__form">
      <div data-toutify-signup data-client-id="leadcrazy" data-login-url="/auth/toutify/login"
           data-title="Create your free account" data-button="Create my free account" data-accent="#0071e3"></div>
      <script src="https://toutify.com/embed/signup.js" async></script>
      <noscript><p class="alert alert--info">The sign-up form needs JavaScript. You can also <a href="/auth/toutify/login?signup=1">sign up on Toutify</a>.</p></noscript>
    </div>
  </div>
</section>
