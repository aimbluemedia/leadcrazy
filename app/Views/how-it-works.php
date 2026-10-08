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
    'other' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
];
$o = static fn (string $k): string => View::e($old[$k] ?? '');
$config = [
    'trades' => Trades::ALL,
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
    <p class="lede" style="margin:0 auto">Click through six quick steps and watch your page take shape. When you like it, publish it &mdash;
      we host it for free at <strong><?= View::e($config['base']) ?>your-business</strong>.</p>
  </div>
</section>

<section class="builder-wrap" id="builder">
  <div class="container builder" data-builder data-config="<?= View::e(json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>">
    <div class="builder__panel">
      <ol class="builder__steps" aria-label="Steps">
        <?php foreach (['Trade', 'Business', 'Services', 'Service area', 'Offer', 'Publish'] as $i => $label): ?>
          <li><button type="button" data-goto="<?= $i + 1 ?>"><span><?= $i + 1 ?></span><?= $label ?></button></li>
        <?php endforeach; ?>
      </ol>

      <noscript><div class="alert alert--info">The builder needs JavaScript. You can still <a href="/members/signup">create your free account</a> and we will build your page for you.</div></noscript>

      <form method="post" action="/members/build" class="builder__form form" novalidate data-builder-form>
        <input type="hidden" name="_ft" value="<?= View::e($token) ?>">
        <input type="hidden" name="builder" value="" data-builder-json>
        <div class="lf-hp" aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden">
          <label>Company website <input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
        </div>

        <!-- 1. Trade -->
        <fieldset class="bstep" data-step="1">
          <legend><span class="bstep__n">Step 1 of 6</span>What kind of business are you?</legend>
          <p class="bstep__hint">We will fill in common services, budgets and an offer for your trade. You can change all of it.</p>
          <div class="trades">
            <?php foreach (Trades::ALL as $key => $t): ?>
              <button type="button" class="trade" data-trade="<?= $key ?>" aria-pressed="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icons[$key] ?></svg>
                <span><?= View::e($t['label']) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
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

        <!-- 6. Publish -->
        <fieldset class="bstep" data-step="6" hidden>
          <legend><span class="bstep__n">Step 6 of 6</span>Publish your page &mdash; free</legend>
          <p class="bstep__hint">Create your free account and your page goes live right away at:</p>
          <p class="slug-preview" data-slug-preview></p>
          <?php if ($error !== null): ?><div class="alert" role="alert"><?= View::e($error) ?></div><?php endif; ?>
          <div class="form__row">
            <div><label for="p-first">First name</label><input id="p-first" name="first_name" type="text" maxlength="80" required autocomplete="given-name" value="<?= $o('first_name') ?>"></div>
            <div><label for="p-last">Last name</label><input id="p-last" name="last_name" type="text" maxlength="80" autocomplete="family-name" value="<?= $o('last_name') ?>"></div>
          </div>
          <div><label for="p-email">Email</label><input id="p-email" name="email" type="email" maxlength="254" required autocomplete="email" value="<?= $o('email') ?>"><p class="hint">New leads show up in your dashboard; you sign in with this.</p></div>
          <div><label for="p-pass">Password</label><input id="p-pass" name="password" type="password" minlength="10" required autocomplete="new-password"><p class="hint">At least 10 characters.</p></div>
          <label class="check"><input type="checkbox" name="agree" value="1" required> <span>I agree to the <a href="/terms" target="_blank">terms</a> and <a href="/privacy" target="_blank">privacy policy</a>.</span></label>
          <button class="btn btn--primary btn--xl btn--block" type="submit" data-publish>Publish my free page</button>
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
            <p class="pv__label">Choose your services</p>
            <div class="pv__checks" data-pv="services"></div>
            <p class="pv__label">Budget</p>
            <i class="pv__line pv__select" data-pv="budget"></i>
            <span class="pv__btn">Get My Free Quote &rarr;</span>
          </div>
          <div class="pv__area" data-pv="area"></div>
        </div>
      </div>
    </aside>
  </div>
</section>

<section class="section section--soft">
  <div class="container">
    <h2 class="center">What happens after you publish</h2>
    <ol class="steps" style="margin-top:28px">
      <li><h3>Your page is live</h3><p>Right away, at your own LeadCrazy address. Share the link anywhere.</p></li>
      <li><h3>Leads come in</h3><p>Each request shows the services, timeline and budget they picked.</p></li>
      <li><h3>Work them in one place</h3><p>Mark leads contacted, quoted, won or lost, and export to CSV.</p></li>
      <li><h3>Grow when ready</h3><p>Add photos, reviews and more offers, or put the form on your own site.</p></li>
    </ol>
    <p class="center" style="margin-top:28px"><a class="btn btn--ghost" href="/pricing">Compare plans</a></p>
  </div>
</section>
<script src="<?= View::e(View::asset('/assets/js/builder.js')) ?>" defer></script>
