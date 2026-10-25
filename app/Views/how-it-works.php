<?php
/**
 * /how-it-works: the free lead form builder.
 *
 * Six short steps on the left, a live preview of the page on the right. The
 * whole state travels as one JSON field and is checked again on the server
 * (BuilderController::publish), so nothing typed here is trusted as-is.
 *
 * @var array<string,mixed> $state  Builder state to restore after a failed publish.
 * @var array<string,string> $old   Account fields to restore (never the password).
 * @var ?string $error
 * @var string $token               Signed form token (FormToken, slug "builder").
 */
use App\Support\Pages;
use App\Support\Plans;
use App\Support\Trades;
use App\Support\View;

$icons = [
    'landscaping' => '<path d="M11 20A7 7 0 0 1 4 13c0-6 5-9 16-9 0 11-3 16-9 16z"/><path d="M4 20c4-4 7-6 10-7"/>',
    'hvac' => '<path d="M12 2v20M4.9 4.9l14.2 14.2M2 12h20M4.9 19.1 19.1 4.9"/>',
    'plumbing' => '<path d="M12 2.7s7 7.3 7 12.3a7 7 0 0 1-14 0c0-5 7-12.3 7-12.3z"/>',
    'roofing' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
    'remodeling' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"/>',
    'cleaning' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/><path d="M19 17l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7z"/>',
    'painting' => '<rect x="3" y="3" width="15" height="6" rx="1.5"/><path d="M18 6h3v5h-9v3"/><path d="M12 14v7"/>',
    'pools' => '<path d="M2 7c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/><path d="M2 13c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/><path d="M2 19c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/>',
    'electrical' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
    'lawyers' => '<path d="M12 3v18M7 21h10"/><path d="M5 7h14"/><path d="M5 7l-3 7a3 3 0 0 0 6 0L5 7zM19 7l-3 7a3 3 0 0 0 6 0l-3-7z"/>',
    'mortgage' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M14 12.5c-.5-.7-1.3-1-2-1-1.1 0-2 .6-2 1.5s.9 1.3 2 1.5 2 .6 2 1.5-.9 1.5-2 1.5c-.8 0-1.5-.3-2-1M12 10.5v1M12 17v1"/>',
    'real_estate' => '<path d="M3 21V9l9-6 9 6v12"/><path d="M9 21v-6h6v6"/><path d="M15 3h4v4"/>',
    'insurance' => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/><path d="m9 12 2 2 4-4"/>',
    'plastic_surgery' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/><circle cx="12" cy="10" r="1"/>',
    'med_spa' => '<path d="M12 21c-4 0-7-3-7-7 0-4.5 7-11 7-11s7 6.5 7 11c0 4-3 7-7 7z"/><path d="M9 14a3 3 0 0 0 3 3"/>',
    'tax' => '<path d="M6 2h9l4 4v16H6z"/><path d="M15 2v4h4"/><path d="m9 17 6-6"/><circle cx="9.5" cy="11.5" r="1"/><circle cx="14.5" cy="16.5" r="1"/>',
    'solar' => '<path d="M3 20h18"/><path d="M4 16 6 9h12l2 7z"/><path d="M10 9l-.7 7M14 9l.7 7M5 12.5h14"/><circle cx="18" cy="4" r="1.5"/>',
    'dental' => '<path d="M7 3c-2.5 0-4 2-4 4.5 0 3 1.5 4.5 2 7 .4 2.3 1 6.5 2.5 6.5S9 18 10 16c.6-1.2 1.4-1.2 2-1.2s1.4 0 2 1.2c1 2 1 5 2.5 5S18.6 16.8 19 14.5c.5-2.5 2-4 2-7C21 5 19.5 3 17 3c-2 0-3 1-5 1S9 3 7 3z"/>',
    'accounting' => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 11h2M14 11h2M8 15h2M14 15h2M8 19h2M14 19h2"/>',
    'other' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
];
$o = static fn (string $k): string => View::e($old[$k] ?? '');
$config = [
    'trades' => Trades::forBuilder(),
    'limits' => ['cities' => Plans::FREE_CITIES, 'zips' => Plans::FREE_ZIPS, 'services' => 15],
    'timelines' => Pages::DEFAULT_TIMELINES,
    'base' => rtrim(preg_replace('#^https?://#', '', View::url('/')), '/') . '/',
    'state' => $state,
    'step' => $error !== null ? 6 : 1,
];
?>
<section class="section builder-hero">
  <div class="container center">
    <span class="eyebrow">Free lead form builder</span>
    <h1>Build your lead form. <span class="accent">Free.</span></h1>
    <p class="lede" style="margin:0 auto">Click through six quick steps and watch your page take shape. When you like it, submit it &mdash;
      once approved we host it for free at <strong><?= View::e($config['base']) ?>your-business</strong>.</p>
  </div>
</section>

<section class="builder-wrap" id="builder">
  <div class="container builder" data-builder data-config="<?= View::e(json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>">
    <div class="builder__panel">
      <ol class="builder__steps" aria-label="Steps">
        <?php foreach (['Trade', 'Business', 'Services', 'Service area', 'Offer', 'Submit'] as $i => $label): ?>
          <li><button type="button" data-goto="<?= $i + 1 ?>"><span><?= $i + 1 ?></span><?= $label ?></button></li>
        <?php endforeach; ?>
      </ol>

      <?php if ($error !== null): ?>
        <div class="alert builder__banner" role="alert" id="builder-error"><strong>Your lead form was not submitted yet.</strong> <?= View::e($error) ?></div>
      <?php endif; ?>
      <noscript><div class="alert alert--info">The builder needs JavaScript. You can still <a href="/members/signup">create your free account</a> and we will build your page for you.</div></noscript>

      <form method="post" action="/members/build" class="builder__form form" novalidate data-builder-form>
        <input type="hidden" name="_ft" value="<?= View::e($token) ?>">
        <input type="hidden" name="builder" value="" data-builder-json>
        <div class="lf-hp" aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden">
          <label>Leave this empty <input type="text" name="lc_trap" value="" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore data-bwignore data-form-type="other"></label>
        </div>

        <!-- 1. Trade -->
        <fieldset class="bstep" data-step="1">
          <legend><span class="bstep__n">Step 1 of 6</span>What kind of business are you?</legend>
          <p class="bstep__hint">We will fill in common services, wording and an offer for your industry. You can change all of it.</p>
          <?php foreach (Trades::GROUPS as $group => $heading): ?>
            <p class="trades__group"><?= View::e($heading) ?></p>
            <div class="trades">
              <?php foreach (Trades::ALL as $key => $t): ?>
                <?php if (($t['group'] ?? 'home') !== $group) { continue; } ?>
                <button type="button" class="trade" data-trade="<?= $key ?>" aria-pressed="false">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icons[$key] ?? $icons['other'] ?></svg>
                  <span><?= View::e($t['label']) ?></span>
                </button>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </fieldset>

        <!-- 2. Business -->
        <fieldset class="bstep" data-step="2" hidden>
          <legend><span class="bstep__n">Step 2 of 6</span>Tell us about your business</legend>
          <div><label for="b-name">Business name</label><input id="b-name" type="text" maxlength="160" data-field="business" placeholder="Storm Landscaping" autocomplete="organization"></div>
          <div><label for="b-phone">Phone customers can call</label><input id="b-phone" type="tel" maxlength="40" data-field="phone" placeholder="(602) 555-0123" autocomplete="tel"></div>
          <div class="form__row">
            <div><label for="b-city">City</label><input id="b-city" type="text" maxlength="80" data-field="city" placeholder="Phoenix" autocomplete="address-level2"></div>
            <div><label for="b-state">State</label><input id="b-state" type="text" maxlength="40" data-field="state" placeholder="AZ" autocomplete="address-level1"></div>
          </div>
          <div data-license hidden><label for="b-license"><span data-license-label>License #</span> <span class="count" data-license-note>optional</span></label><input id="b-license" type="text" maxlength="80" data-field="license" placeholder="Shown on your page"><p class="hint">Shown on your page and in the form's fine print.</p></div>
          <div><label for="b-web">Website <span class="count">optional</span></label><input id="b-web" type="text" inputmode="url" maxlength="300" data-field="website" placeholder="stormlandscaping.com" autocomplete="url"></div>
          <div class="exists-note" data-exists hidden>
            <strong>Looks like you already have a LeadCrazy profile.</strong>
            <p>We found a profile matching this business name or website. Log in to view it and pick up where you left off.</p>
            <a class="btn btn--primary btn--sm" href="/members/login">Log in and view my profile</a>
          </div>
        </fieldset>

        <!-- 3. Services -->
        <fieldset class="bstep" data-step="3" hidden>
          <legend><span class="bstep__n">Step 3 of 6</span>Which services do you offer?</legend>
          <p class="bstep__hint">Tap to turn them on or off. These become the checkboxes on your form.</p>
          <div class="toggles" data-services></div>
          <div class="adder">
            <input type="text" maxlength="60" placeholder="Add another service" data-add-input="services" aria-label="Add a service">
            <button type="button" class="btn btn--ghost btn--sm" data-add="services">Add</button>
          </div>
        </fieldset>

        <!-- 4. Service area -->
        <fieldset class="bstep" data-step="4" hidden>
          <legend><span class="bstep__n">Step 4 of 6</span>Where do you work?</legend>
          <p class="bstep__hint">Free pages list up to <?= Plans::FREE_CITIES ?> cities and <?= Plans::FREE_ZIPS ?> zip codes. Pro and Premium are unlimited.</p>
          <div>
            <label>Cities <span class="count" data-count="cities"></span></label>
            <div class="toggles" data-list="cities"></div>
            <div class="adder">
              <input type="text" maxlength="60" placeholder="Add a city" data-add-input="cities" aria-label="Add a city">
              <button type="button" class="btn btn--ghost btn--sm" data-add="cities">Add</button>
            </div>
          </div>
          <div>
            <label>Zip codes <span class="count" data-count="zips"></span></label>
            <div class="toggles" data-list="zips"></div>
            <div class="adder">
              <input type="text" inputmode="numeric" maxlength="5" placeholder="85001" data-add-input="zips" aria-label="Add a zip code">
              <button type="button" class="btn btn--ghost btn--sm" data-add="zips">Add</button>
            </div>
          </div>
        </fieldset>

        <!-- 5. Offer -->
        <fieldset class="bstep" data-step="5" hidden>
          <legend><span class="bstep__n">Step 5 of 6</span>Add a special offer</legend>
          <p class="bstep__hint">An offer on top of the form gives people a reason to ask for a quote today. Free pages show one.</p>
          <label class="switch"><input type="checkbox" data-field="offer.on"><span></span> Show an offer on my page</label>
          <div data-offer-fields class="stack">
            <div><label for="o-title">Offer title</label><input id="o-title" type="text" maxlength="120" data-field="offer.title"></div>
            <div><label for="o-body">Details</label><textarea id="o-body" rows="3" maxlength="600" data-field="offer.body"></textarea></div>
            <div style="max-width:220px"><label for="o-exp">Ends on</label><input id="o-exp" type="date" data-field="offer.expires"></div>
          </div>
        </fieldset>

        <!-- 6. Submit -->
        <fieldset class="bstep" data-step="6" hidden>
          <legend><span class="bstep__n">Step 6 of 6</span>Submit your lead form &mdash; free</legend>
          <p class="bstep__hint">Create your free account. Once our team approves your page, it goes live at:</p>
          <p class="slug-preview" data-slug-preview></p>
          <div class="form__row">
            <div><label for="p-first">First name</label><input id="p-first" name="first_name" type="text" maxlength="80" required autocomplete="given-name" value="<?= $o('first_name') ?>"></div>
            <div><label for="p-last">Last name</label><input id="p-last" name="last_name" type="text" maxlength="80" autocomplete="family-name" value="<?= $o('last_name') ?>"></div>
          </div>
          <div><label for="p-email">Email</label><input id="p-email" name="email" type="email" maxlength="254" required autocomplete="email" value="<?= $o('email') ?>" data-email-check><p class="field-error" data-email-msg<?= empty($old['email_taken']) ? ' hidden' : '' ?>><?= View::e(App\Controllers\BuilderController::EMAIL_TAKEN) ?> <a href="/members/login">Sign in</a></p><p class="hint">New leads show up in your dashboard; you sign in with this.</p></div>
          <div><label for="p-pass">Password</label><input id="p-pass" name="password" type="password" minlength="10" required autocomplete="new-password"><p class="hint">At least 10 characters.</p></div>
          <label class="check"><input type="checkbox" name="agree" value="1" required> <span>I agree to the <a href="/terms" target="_blank">terms</a> and <a href="/privacy" target="_blank">privacy policy</a>.</span></label>
          <button class="btn btn--primary btn--xl btn--block" type="submit" data-publish>Submit my free lead form</button>
          <p class="hint center">Free forever &middot; <?= Plans::FREE_MONTHLY_LEADS ?> leads a month &middot; no credit card &middot; upgrade any time</p>
          <p class="hint center">Already have an account? <a href="/members/login">Sign in</a>.</p>
        </fieldset>

        <p class="builder__error" role="alert" data-step-error hidden></p>
        <div class="builder__nav">
          <button type="button" class="btn btn--ghost" data-back>Back</button>
          <button type="button" class="btn btn--primary" data-next>Continue</button>
        </div>
      </form>
    </div>

    <aside class="builder__preview" aria-label="Live preview">
      <p class="builder__preview-label">Live preview</p>
      <div class="pv">
        <div class="pv__bar"><span></span><span></span><span></span><em data-pv="url"></em></div>
        <div class="pv__page">
          <span class="pv__badge" data-pv="badge"></span>
          <h3 class="pv__title" data-pv="headline"></h3>
          <p class="pv__sub" data-pv="serving"></p>
          <div class="pv__offer" data-pv="offer">
            <span class="pv__offer-tag">Limited time</span>
            <strong data-pv="offer-title"></strong>
            <p data-pv="offer-body"></p>
            <small data-pv="offer-exp"></small>
          </div>
          <div class="pv__form">
            <p class="pv__form-title" data-pv="form-title"></p>
            <div class="pv__row"><i>First name</i><i>Last name</i></div>
            <i class="pv__line">Phone</i>
            <i class="pv__line">Email</i>
            <p class="pv__label" data-pv="services-label">Choose your services</p>
            <div class="pv__checks" data-pv="services"></div>
            <div data-pv="budget-wrap"><p class="pv__label" data-pv="budget-label">Budget</p>
            <i class="pv__line pv__select" data-pv="budget"></i></div>
            <span class="pv__btn"><span data-pv="cta">Get My Free Quote</span> &rarr;</span>
            <p class="pv__fine" data-pv="fine" hidden></p>
          </div>
          <div class="pv__area" data-pv="area"></div>
        </div>
      </div>
    </aside>
  </div>
</section>

<section class="section section--soft">
  <div class="container">
    <h2 class="center">What happens after you submit</h2>
    <ol class="steps" style="margin-top:28px">
      <li><h3>Finish your account</h3><p>Log in and add your logo, photos and a few words about your business.</p></li>
      <li><h3>We approve it</h3><p>Usually within one business day. Then it is live at your own LeadCrazy address.</p></li>
      <li><h3>Leads come in</h3><p>Each request shows the services, timeline and budget they picked.</p></li>
      <li><h3>Work them in one place</h3><p>Mark leads contacted, quoted, won or lost, and export to CSV.</p></li>
      
    </ol>
    <p class="center" style="margin-top:28px"><a class="btn btn--ghost" href="/pricing">Compare plans</a></p>
  </div>
</section>
<script src="<?= View::e(View::asset('/assets/js/builder.js')) ?>" defer></script>
