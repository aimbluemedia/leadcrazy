<?php
/**
 * Hero band at the top of the homepage: headline and calls to action on the
 * left; on the right, like ContentVendor's, a photo of a business owner with
 * lead activity floating around it, and the sister products a membership
 * includes underneath.
 *
 * The floating cards are illustrations and are captioned as such.
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
      <p class="hero-band__eyebrow">Every Visitor Is Your Potential Next Client.</p>
      <h1 class="hero-band__title">The Million Dollar <em>Lead Form.</em></h1>
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
      <div class="hero-photo">
        <div class="hero-photo__frame">
          <img src="<?= View::e(View::asset('/assets/img/hero-bakery.webp')) ?>" width="1448" height="1086" fetchpriority="high"
               alt="A smiling bakery owner at her laptop in her shop">
        </div>

        <div class="hcard hcard--pill hcard--c1" aria-hidden="true">
          <span class="hcard__ico hcard__ico--dark"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg></span>
          New lead &middot; Wedding cake
        </div>

        <div class="hcard hcard--c2" aria-hidden="true">
          <p class="hcard__head"><span class="hcard__ico hcard__ico--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v9H4v-9M2 7h20v5H2zM12 21V7M12 7H7.5a2.5 2.5 0 1 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 1 0 0-5C13 2 12 7 12 7z"/></svg></span>Offer claimed</p>
          <div class="hcard__offer">
            <span class="hcard__thumb"></span>
            <span><strong>Free dozen cookies</strong> with any catering order this month!</span>
          </div>
          <p class="hcard__meta"><span>Limited time</span><span>Expires Dec 31</span></p>
        </div>

        <div class="hcard hcard--c3" aria-hidden="true">
          <p class="hcard__head"><span class="hcard__ico hcard__ico--blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6l1 2h3v16H5V5h3z"/><path d="M9 12l2 2 4-4"/></svg></span>Quote request</p>
          <p class="hcard__q">&ldquo;What do you need?&rdquo;</p>
          <p class="hcard__a"><b>&#10003; Custom cake</b><b>&#10003; Catering</b></p>
          <p class="hcard__q">Budget <strong>$250 &ndash; $500</strong> &middot; Date <strong>Dec 14</strong></p>
        </div>

        <div class="hcard hcard--pill hcard--c4" aria-hidden="true">
          <span class="hcard__ico hcard__ico--red"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 5.3 7 13 7 13s7-7.7 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg></span>
          Gilbert, AZ &middot; 85234
        </div>

        <div class="hcard hcard--c5" aria-hidden="true">
          <span class="hcard__g">G</span>
          <span><strong>New 5-star Google review</strong><small><em>&#9733;&#9733;&#9733;&#9733;&#9733;</em> 4.9 &middot; Just now</small></span>
        </div>

        <div class="hcard hcard--c6" aria-hidden="true">
          <span class="hcard__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5 10 17 19 7"/></svg></span>
          <span><strong>New lead: quote request</strong><small>From your website &middot; 2 min ago</small></span>
        </div>
      </div>

      <div class="hero-members">
        <p class="hero-members__label"><span class="hcard__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5 10 17 19 7"/></svg></span>Membership includes access to</p>
        <div class="hero-members__grid">
          <?php foreach ([
            ['Search<wbr>Monster', 'AI search &amp; free listing', '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>'],
            ['Monster<wbr>List', 'Free listings everywhere', '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/>'],
            ['Promo<wbr>Monster', 'Reviews &amp; reputation', '<path d="m12 3 2.7 5.6 6.1.8-4.5 4.2 1.1 6.1L12 16.8 6.6 19.7l1.1-6.1-4.5-4.2 6.1-.8z"/>'],
            ['Content<wbr>Vendor', 'AI posts &amp; content', '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>'],
            ['Official Directories', 'Free state directory listings', '<path d="M3 21h18M4 10h16M12 3 3 8v2h18V8z"/><path d="M6 10v8M10 10v8M14 10v8M18 10v8"/>'],
          ] as [$name, $what, $icon]): ?>
            <div class="hero-member">
              <span class="hero-member__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icon ?></svg></span>
              <strong><?= $name ?></strong>
              <small><?= $what ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <p class="hero-band__art-note">Example activity, for illustration</p>
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
