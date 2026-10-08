<?php
/**
 * Dark hero band at the top of the homepage. Same structure as PromoMonster's
 * (strip, headline, two calls to action, chips, illustration, fact panel), but
 * the illustration is the product: a lead form with new leads arriving.
 *
 * The lead cards are illustrations and are captioned as such. No invented
 * traction numbers either: the panel only carries things a visitor can check.
 */
use App\Support\View;
?>
<div class="hero-band">
  <?php for ($i = 1; $i <= 6; $i++): ?>
    <span class="sparkle sparkle--<?= $i ?>" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 1.5c.6 5.6 4.9 9.9 10.5 10.5-5.6.6-9.9 4.9-10.5 10.5-.6-5.6-4.9-9.9-10.5-10.5C7.1 11.4 11.4 7.1 12 1.5z"/></svg></span>
  <?php endfor; ?>
  <div class="hero-band__strip">
    <div class="container hero-band__strip-inner">
      <span>A lead page built for you &middot; Offers, photos, reviews &amp; service areas &middot; Every lead in one dashboard</span>
      <span class="hero-band__strip-right">Start free. No credit card.</span>
    </div>
  </div>

  <div class="container hero-band__inner">
    <div class="hero-band__copy">
      <p class="hero-band__eyebrow">The Million Dollar Lead Form</p>
      <h1 class="hero-band__title">Every Visitor Is Your Potential <em>Next Client.</em></h1>
      <p class="hero-band__sub">Do You Have the Right Form to Close Them?</p>
      <p class="hero-band__lede">Most local business websites make customers hunt for a phone number. LeadCrazy gives you a
        lead page that asks the right questions &mdash; services, timeline, budget &mdash; and puts your best offer
        right on top of the form. Every request lands in your dashboard, ready to call.</p>
      <div class="hero-band__cta">
        <a class="btn btn--primary btn--xl" href="/members/signup">Get My Free Lead Page &rarr;</a>
        <a class="btn hero-band__ghost btn--xl" href="/how-it-works">&#9733; See How It Works</a>
      </div>
      <p class="hero-band__note">Takes about two minutes. Our team builds the page.</p>
      <div class="hero-band__chips">
        <span class="hero-band__chips-label">Share it on:</span>
        <?php foreach (['&#127760; Your website', '&#128205; Google Business', '&#128172; Facebook', '&#128203; MonsterList', '&#128241; Text &amp; QR'] as $chip): ?>
          <span class="hero-chip"><?= $chip ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="hero-band__art">
      <div class="lead-stage" aria-hidden="true">
        <div class="lead-glow"></div>
        <div class="lead-form">
          <div class="lead-form__offer"><span>Limited time</span><strong>Spring Backyard Special</strong><em>Free design + 10% off</em></div>
          <p class="lead-form__title">Get Your Free Quote</p>
          <div class="lead-form__row"><i></i><i></i></div>
          <i class="lead-form__line"></i>
          <div class="lead-form__checks"><b>&#10003; Pavers</b><b>Turf</b><b>&#10003; Lighting</b><b>Fire pit</b></div>
          <i class="lead-form__line"></i>
          <div class="lead-form__btn">Get My Free Quote &rarr;</div>
        </div>
        <?php foreach ([
          ['n1', 'New lead', 'Dana R. &middot; Tempe', 'Pavers &middot; $10k&ndash;25k'],
          ['n2', 'New lead', 'Marcus T. &middot; Mesa', 'Artificial turf &middot; ASAP'],
          ['n3', 'Offer claimed', 'Priya S. &middot; Gilbert', 'Spring Backyard Special'],
          ['n4', 'New lead', 'Jordan L. &middot; Chandler', 'Lighting &middot; 1&ndash;3 months'],
        ] as [$pos, $kind, $who, $what]): ?>
          <div class="lead-pop lead-pop--<?= $pos ?>">
            <span class="lead-pop__dot"></span>
            <span><strong><?= $kind ?></strong><em><?= $who ?></em><small><?= $what ?></small></span>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="hero-band__art-note">Example leads, for illustration</p>
    </div>
  </div>

  <div class="container">
    <div class="hero-panel">
      <p class="hero-panel__label">What you get</p>
      <div class="hero-panel__grid">
        <?php foreach ([
          ['&#10003;', 'Free', 'To start. No credit card, no contract.'],
          ['<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>', '1 day', 'Typical build time. Our team writes and designs the page.'],
          ['&lt;/&gt;', '1 line', 'Of code puts it on your own website (Premium).'],
        ] as [$icon, $value, $label]): ?>
          <div class="hero-stat">
            <span class="hero-stat__tile"><?= $icon ?></span>
            <span><span class="hero-stat__v"><?= $value ?></span><span class="hero-stat__l"><?= View::e(html_entity_decode($label)) ?></span></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="ticker" role="presentation">
  <div class="ticker__track">
    <?php for ($copy = 0; $copy < 2; $copy++): ?>
      <div class="ticker__group"<?= $copy ? ' aria-hidden="true"' : '' ?>>
        <?php foreach (['Built for you in a day', 'Offers on top of the form', 'Services, timeline &amp; budget on every lead', 'Listed on MonsterList', 'Embed on your own site', 'Start free, no card'] as $claim): ?>
          <span><?= $claim ?></span>
        <?php endforeach; ?>
      </div>
    <?php endfor; ?>
  </div>
</div>
