<?php
/** @var array<string,mixed> $data @var array<string,mixed> $page @var array<string,mixed> $account @var ?string $submittedAt */
use App\Support\Csrf;
use App\Support\Plans;
use App\Support\View;
$v = static fn (string $k, string $fallback = ''): string => View::e(is_string($data[$k] ?? null) && $data[$k] !== '' ? $data[$k] : $fallback);
$full = Plans::isPaid((string) $account['plan']) || Plans::isPaid((string) ($account['requested_plan'] ?? ''));
?>
<div class="app__head"><h1>Tell us about your business</h1></div>
<p class="lede" style="margin-bottom:20px">We build your Million Dollar Lead Form page from this. Answer what you can &mdash; we will fill gaps and you can ask for changes any time.
<?php if ($submittedAt): ?><br><span class="muted" style="font-size:14px">Last sent <?= View::e(date('M j, Y', strtotime((string) $submittedAt) ?: 0)) ?>. Sending again adds to what we have.</span><?php endif; ?></p>

<form class="form" method="post" action="/members/intake" enctype="multipart/form-data">
  <?= Csrf::field() ?>
  <div class="panel">
    <h2>The basics</h2>
    <div class="stack">
      <div><label for="business_name">Business name</label><input id="business_name" name="business_name" type="text" maxlength="160" value="<?= $v('business_name', (string) $account['business_name']) ?>"></div>
      <div class="form__row">
        <div><label for="phone">Phone for customers</label><input id="phone" name="phone" type="tel" value="<?= $v('phone', (string) ($page['phone'] ?? '')) ?>"></div>
        <div><label for="public_email">Email for customers</label><input id="public_email" name="public_email" type="email" value="<?= $v('public_email', (string) ($page['public_email'] ?? '')) ?>"></div>
      </div>
      <div class="form__row form__row--3">
        <div><label for="city">City</label><input id="city" name="city" type="text" value="<?= $v('city') ?>"></div>
        <div><label for="state">State</label><input id="state" name="state" type="text" value="<?= $v('state') ?>" placeholder="AZ"></div>
        <div><label for="years">In business since</label><input id="years" name="years" type="text" value="<?= $v('years') ?>" placeholder="1998"></div>
      </div>
      <div><label for="website">Current website (if any)</label><input id="website" name="website" type="url" value="<?= $v('website') ?>" placeholder="https://"></div>
    </div>
  </div>

  <div class="panel">
    <h2>What you do and where</h2>
    <div class="stack">
      <div><label for="services">Services you offer</label><textarea id="services" name="services" rows="5" placeholder="Pavers&#10;Artificial Turf&#10;Outdoor Lights&#10;Fire Pits"><?= $v('services') ?></textarea><p class="hint">One per line. These become the checkboxes on your form.</p></div>
      <div><label for="locations">Cities you serve</label><textarea id="locations" name="locations" rows="3" placeholder="Phoenix, Tempe, Mesa, Chandler, Gilbert"><?= $v('locations') ?></textarea></div>
      <div><label for="zipcodes">Zip codes you serve<?= $full ? '' : ' (shown on Pro and Premium)' ?></label><textarea id="zipcodes" name="zipcodes" rows="3" placeholder="85001, 85003 - 85009, 85012"><?= $v('zipcodes') ?></textarea></div>
    </div>
  </div>

  <div class="panel">
    <h2>Your story</h2>
    <div class="stack">
      <div><label for="about">About your business</label><textarea id="about" name="about" rows="5" placeholder="Who you are, how long you've been doing it, what makes your work different."><?= $v('about') ?></textarea></div>
      <div><label for="why">Why customers choose you</label><textarea id="why" name="why" rows="4" placeholder="Transparent pricing with no surprises&#10;Custom designs for your budget&#10;Licensed and insured"><?= $v('why') ?></textarea><p class="hint">One reason per line.</p></div>
    </div>
  </div>

  <div class="panel">
    <h2>Offers and reviews<?= $full ? '' : ' <span class="tag tag--pro">Pro &amp; Premium</span>' ?></h2>
    <div class="stack">
      <div><label for="offers">Special offers</label><textarea id="offers" name="offers" rows="5" placeholder="Name of the offer, what's included, the price and when it expires."><?= $v('offers') ?></textarea></div>
      <div><label for="testimonials">Customer testimonials</label><textarea id="testimonials" name="testimonials" rows="5" placeholder="Paste real reviews from customers, with their first name and city."><?= $v('testimonials') ?></textarea><p class="hint">Only real reviews from real customers, please &mdash; we show them as provided by you.</p></div>
      <div><label for="video_url">YouTube or Vimeo video link</label><input id="video_url" name="video_url" type="url" value="<?= $v('video_url') ?>" placeholder="https://youtube.com/watch?v=..."></div>
    </div>
  </div>

  <div class="panel">
    <h2>Logo and photos</h2>
    <div class="stack">
      <div><label for="logo">Logo</label><input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp,image/gif"></div>
      <div><label for="photos">Project photos (up to 12)</label><input id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple><p class="hint">JPG, PNG or WebP, up to 6 MB each. Your best photo goes at the top of your page.</p></div>
    </div>
  </div>

  <div class="panel">
    <h2>Anything else?</h2>
    <textarea name="notes" rows="3" placeholder="Colors you like, pages you admire, anything we should know."><?= $v('notes') ?></textarea>
  </div>

  <div><button class="btn btn--primary" type="submit">Send to the LeadCrazy team &rarr;</button>
  <?php if (Plans::isPaid((string) ($account['requested_plan'] ?? '')) && !Plans::isPaid((string) $account['plan'])): ?>
    <p class="hint">Next, you'll go to secure checkout for <?= View::e(Plans::name((string) $account['requested_plan'])) ?> (<?= View::e(Plans::priceLabel((string) $account['requested_plan'])) ?>).</p>
  <?php endif; ?></div>
</form>
