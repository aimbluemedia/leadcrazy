<?php
/**
 * The Million Dollar Lead Form. Shared by the hosted page, both embeds and the
 * superadmin preview.
 *
 * @var array<string,mixed> $account @var array<string,mixed> $page
 * @var array<string,mixed> $form   @var list<array<string,mixed>> $offers
 * @var list<string> $formServices @var list<string> $timelines @var list<string> $budgets
 * @var bool $full @var string $mode
 */
use App\Support\Turnstile;
use App\Support\View;

$e = $form['errors'];
$old = $form['old'];
$val = static fn (string $k): string => View::e(is_string($old[$k] ?? null) ? $old[$k] : '');
$err = static fn (string $k): string => isset($e[$k]) ? '<p class="lf-error" id="err-' . $k . '">' . View::e($e[$k]) . '</p>' : '';
$inv = static fn (string $k): string => isset($e[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
$chosen = array_map('strval', (array) ($old['services'] ?? []));
$name = (string) $account['business_name'];
$slug = (string) $account['slug'];
$isPreview = $mode === 'preview';
?>
<?php if ($form['thanks']): ?>
  <div class="lf-thanks" role="status">
    <div class="lf-thanks__icon" aria-hidden="true">&#10003;</div>
    <h2>Thank you! Your request is in.</h2>
    <p><?= View::e($name) ?> will be in touch shortly<?= !empty($page['phone']) ? ' &mdash; or call now on <a href="tel:' . View::e(preg_replace('/[^0-9+]/', '', (string) $page['phone'])) . '">' . View::e($page['phone']) . '</a>' : '' ?>.</p>
  </div>
<?php else: ?>
<h2 class="lf-title"><?= View::e($page['form_title'] ?? '' ?: $name) ?></h2>
<p class="lf-intro"><?= View::e($page['form_intro'] ?? '' ?: 'Fill out this quick form and we\'ll provide a detailed quote and design consultation - completely free, no strings attached.') ?></p>

<?php if (isset($e['_form'])): ?>
  <div class="lf-alert" role="alert"><?= View::e($e['_form']) ?></div>
<?php elseif ($e !== []): ?>
  <div class="lf-alert" role="alert">Please check the highlighted fields.</div>
<?php endif; ?>

<form class="lf" method="post" action="/lead/<?= View::e(rawurlencode($slug)) ?>" novalidate
      <?= $isPreview ? 'onsubmit="alert(\'Preview only - this form does not send.\');return false;"' : '' ?>>
  <input type="hidden" name="_ft" value="<?= View::e($form['token']) ?>">
  <input type="hidden" name="via" value="<?= View::e($mode === 'preview' ? 'hosted' : $mode) ?>">
  <div class="lf-hp" aria-hidden="true">
    <label>Company website <input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
  </div>

  <div class="lf-row">
    <div class="lf-field">
      <label for="lf-first">First Name <span>*</span></label>
      <input id="lf-first" name="first_name" type="text" autocomplete="given-name" placeholder="John" required value="<?= $val('first_name') ?>"<?= $inv('first_name') ?>>
      <?= $err('first_name') ?>
    </div>
    <div class="lf-field">
      <label for="lf-last">Last Name <span>*</span></label>
      <input id="lf-last" name="last_name" type="text" autocomplete="family-name" placeholder="Smith" required value="<?= $val('last_name') ?>"<?= $inv('last_name') ?>>
      <?= $err('last_name') ?>
    </div>
  </div>

  <div class="lf-field">
    <label for="lf-email">Email Address <span>*</span></label>
    <input id="lf-email" name="email" type="email" autocomplete="email" placeholder="johnsmith@email.com" required value="<?= $val('email') ?>"<?= $inv('email') ?>>
    <?= $err('email') ?>
  </div>

  <div class="lf-field">
    <label for="lf-phone">Phone Number <span>*</span></label>
    <input id="lf-phone" name="phone" type="tel" autocomplete="tel" placeholder="(555) 123-4567" required value="<?= $val('phone') ?>"<?= $inv('phone') ?>>
    <p class="lf-hint">We'll only use this to contact you about your quote.</p>
    <?= $err('phone') ?>
  </div>

  <div class="lf-row">
    <div class="lf-field">
      <label for="lf-city">City <span>*</span></label>
      <input id="lf-city" name="city" type="text" autocomplete="address-level2" placeholder="<?= View::e($page['city'] ?? '' ?: 'City') ?>" required value="<?= $val('city') ?>"<?= $inv('city') ?>>
      <?= $err('city') ?>
    </div>
    <div class="lf-field">
      <label for="lf-zip">Zip Code <span>*</span></label>
      <input id="lf-zip" name="zip" type="text" inputmode="numeric" autocomplete="postal-code" placeholder="85001" maxlength="10" required value="<?= $val('zip') ?>"<?= $inv('zip') ?>>
      <?= $err('zip') ?>
    </div>
  </div>

  <?php if ($formServices !== []): ?>
  <fieldset class="lf-field"<?= $inv('services') ?>>
    <legend>Choose Your Services <span>*</span></legend>
    <div class="lf-checks">
      <?php foreach ($formServices as $i => $service): ?>
        <label class="lf-check"><input type="checkbox" name="services[]" value="<?= View::e($service) ?>"<?= in_array($service, $chosen, true) ? ' checked' : '' ?>><span><?= View::e($service) ?></span></label>
      <?php endforeach; ?>
    </div>
    <?= $err('services') ?>
  </fieldset>
  <?php endif; ?>

  <fieldset class="lf-field"<?= $inv('timeline') ?>>
    <legend>When are you looking to start? <span>*</span></legend>
    <div class="lf-checks lf-checks--one">
      <?php foreach ($timelines as $option): ?>
        <label class="lf-check"><input type="radio" name="timeline" value="<?= View::e($option) ?>"<?= ($old['timeline'] ?? null) === $option ? ' checked' : '' ?>><span><?= View::e($option) ?></span></label>
      <?php endforeach; ?>
    </div>
    <?= $err('timeline') ?>
  </fieldset>

  <div class="lf-field">
    <label for="lf-budget">Estimated Budget Range <span>*</span></label>
    <select id="lf-budget" name="budget" required<?= $inv('budget') ?>>
      <option value="">Select a range...</option>
      <?php foreach ($budgets as $option): ?>
        <option value="<?= View::e($option) ?>"<?= ($old['budget'] ?? null) === $option ? ' selected' : '' ?>><?= View::e($option) ?></option>
      <?php endforeach; ?>
    </select>
    <p class="lf-hint">This helps us provide accurate recommendations.</p>
    <?= $err('budget') ?>
  </div>

  <?php if ($offers !== []): ?>
  <fieldset class="lf-field">
    <legend>Choose your Offer</legend>
    <div class="lf-checks lf-checks--one">
      <?php foreach ($offers as $offer): ?>
        <label class="lf-check"><input type="radio" name="offer" value="<?= View::e($offer['title']) ?>"<?= ($old['offer'] ?? null) === $offer['title'] ? ' checked' : '' ?>><span><?= View::e($offer['title']) ?></span></label>
      <?php endforeach; ?>
    </div>
  </fieldset>
  <?php endif; ?>

  <div class="lf-field">
    <label for="lf-message">Tell us about your project <span>*</span></label>
    <textarea id="lf-message" name="message" rows="4" placeholder="What is your vision? Any specific features you want? Current pain points with your space? The more details, the better we can help!"<?= $inv('message') ?>><?= $val('message') ?></textarea>
    <?= $err('message') ?>
  </div>

  <?php if (Turnstile::enabled() && !$isPreview): ?>
    <div class="cf-turnstile" data-sitekey="<?= View::e(Turnstile::siteKey()) ?>" data-theme="light"></div>
  <?php endif; ?>

  <button class="lf-submit" type="submit"><?= View::e($page['cta_label'] ?? '' ?: 'Get My Free Quote & Consultation') ?> &rarr;</button>

  <ul class="lf-trust">
    <li><strong>Free Design Consultation:</strong> Expert advice tailored to your vision</li>
    <li><strong>Detailed Quote:</strong> Transparent pricing with no hidden fees</li>
    <li><strong>No Obligation:</strong> Get expert insights with zero pressure</li>
    <li><strong>Fast Response:</strong> Quick turnaround on your quote</li>
  </ul>
  <p class="lf-legal">&#128274; Your information is secure and will never be sold. By submitting, you agree to be contacted by <?= View::e($name) ?> about your project.</p>
</form>
<?php endif; ?>
