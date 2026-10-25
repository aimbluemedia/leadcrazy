<?php
/**
 * The Million Dollar Lead Form. Shared by the hosted page, both embeds and the
 * superadmin preview.
 *
 * @var array<string,mixed> $account @var array<string,mixed> $page
 * @var array<string,mixed> $form   @var list<array<string,mixed>> $offers
 * @var list<string> $formServices @var list<string> $timelines @var list<string> $budgets
 * @var bool $full @var string $mode
 * @var array<string,mixed> $copy  Wording for the page's industry (Trades::copy).
 * @var bool $askBudget
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
$license = trim((string) ($page['license_number'] ?? ''));
?>
<?php if ($form['thanks']): ?>
  <div class="lf-thanks" role="status">
    <div class="lf-thanks__icon" aria-hidden="true">&#10003;</div>
    <h2>Thank you! Your request is in.</h2>
    <p><?= View::e($name) ?> will be in touch shortly<?= !empty($page['phone']) ? ' &mdash; or call now on <a href="tel:' . View::e(preg_replace('/[^0-9+]/', '', (string) $page['phone'])) . '">' . View::e($page['phone']) . '</a>' : '' ?>.</p>
  </div>
<?php else: ?>
<h2 class="lf-title"><?= View::e($page['form_title'] ?? '' ?: $name) ?></h2>
<p class="lf-intro"><?= View::e($page['form_intro'] ?? '' ?: (string) $copy['form_intro']) ?></p>

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
    <label>Leave this empty <input type="text" name="lc_trap" value="" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore data-bwignore data-form-type="other"></label>
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
    <p class="lf-hint"><?= View::e($copy['phone_hint']) ?></p>
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
    <legend><?= View::e($copy['services_label']) ?> <span>*</span></legend>
    <div class="lf-checks">
      <?php foreach ($formServices as $i => $service): ?>
        <label class="lf-check"><input type="checkbox" name="services[]" value="<?= View::e($service) ?>"<?= in_array($service, $chosen, true) ? ' checked' : '' ?>><span><?= View::e($service) ?></span></label>
      <?php endforeach; ?>
    </div>
    <?= $err('services') ?>
  </fieldset>
  <?php endif; ?>

  <fieldset class="lf-field"<?= $inv('timeline') ?>>
    <legend><?= View::e($copy['timeline_label']) ?> <span>*</span></legend>
    <div class="lf-checks lf-checks--one">
      <?php foreach ($timelines as $option): ?>
        <label class="lf-check"><input type="radio" name="timeline" value="<?= View::e($option) ?>"<?= ($old['timeline'] ?? null) === $option ? ' checked' : '' ?>><span><?= View::e($option) ?></span></label>
      <?php endforeach; ?>
    </div>
    <?= $err('timeline') ?>
  </fieldset>

  <?php if ($askBudget): ?>
  <div class="lf-field">
    <label for="lf-budget"><?= View::e($copy['budget_label']) ?> <span>*</span></label>
    <select id="lf-budget" name="budget" required<?= $inv('budget') ?>>
      <option value="">Select a range...</option>
      <?php foreach ($budgets as $option): ?>
        <option value="<?= View::e($option) ?>"<?= ($old['budget'] ?? null) === $option ? ' selected' : '' ?>><?= View::e($option) ?></option>
      <?php endforeach; ?>
    </select>
    <p class="lf-hint"><?= View::e($copy['budget_hint']) ?></p>
    <?= $err('budget') ?>
  </div>
  <?php endif; ?>

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
    <label for="lf-message"><?= View::e($copy['message_label']) ?><?= $copy['message_required'] ? ' <span>*</span>' : ' <em class="lf-optional">(optional)</em>' ?></label>
    <textarea id="lf-message" name="message" rows="4" maxlength="4000" placeholder="<?= View::e($copy['message_placeholder']) ?>"<?= $inv('message') ?>><?= $val('message') ?></textarea>
    <?= $err('message') ?>
  </div>

  <?php if (Turnstile::enabled() && !$isPreview): ?>
    <div class="cf-turnstile" data-sitekey="<?= View::e(Turnstile::siteKey()) ?>" data-theme="light"></div>
  <?php endif; ?>

  <?php if ($copy['consent']): ?>
  <div class="lf-field">
    <label class="lf-consent"<?= $inv('consent') ?>>
      <input type="checkbox" name="consent" value="1" required<?= !empty($old['consent']) ? ' checked' : '' ?>>
      <span><?= View::e(App\Support\Trades::consentText($name)) ?></span>
    </label>
    <?= $err('consent') ?>
  </div>
  <?php endif; ?>

  <button class="lf-submit" type="submit"><?= View::e($page['cta_label'] ?? '' ?: $copy['cta']) ?> &rarr;</button>

  <ul class="lf-trust">
    <?php foreach ($copy['trust'] as [$head, $text]): ?>
      <li><strong><?= View::e($head) ?>:</strong> <?= View::e($text) ?></li>
    <?php endforeach; ?>
  </ul>
  <p class="lf-legal">&#128274; Your information is secure and will never be sold. By submitting, you agree to be contacted by <?= View::e($name) ?> about your request.</p>
  <?php if ($copy['disclaimer'] !== null || ($license !== '' && $copy['license_label'] !== null)): ?>
    <p class="lf-legal lf-disclaimer"><?= View::e($name) ?><?= $license !== '' && $copy['license_label'] !== null ? ' &middot; ' . View::e($copy['license_label'] . ' ' . $license) : '' ?>. <?= View::e((string) $copy['disclaimer']) ?></p>
  <?php endif; ?>
</form>
<?php endif; ?>
