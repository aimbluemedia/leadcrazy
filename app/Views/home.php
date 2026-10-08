<?php use App\Support\View; ?>
<?= View::render('partials/hero-band') ?>

<section class="section">
  <div class="container split">
    <div class="split__media">
      <img src="<?= View::e(View::asset('/assets/img/hero.jpg')) ?>" width="1122" height="1402" loading="lazy"
           alt="A home service professional outside a customer&rsquo;s home, holding a tablet.">
      <div class="split__float" aria-hidden="true">
        <span class="split__dot"></span>
        <span><strong>New lead &middot; Pavers &middot; $10k&ndash;25k</strong>
          <small>Dana R. &middot; Tempe, AZ &middot; starting in 1&ndash;3 months</small></span>
      </div>
    </div>

    <div>
      <h2 class="split__title">Capture. Qualify. <em>Close.</em></h2>
      <p class="lede">Everything a lead page needs, nothing it doesn&rsquo;t. Built from the layout that wins jobs for
        contractors, landscapers, remodelers and home-service pros &mdash; so every visitor knows exactly what to do next.</p>

      <ol class="features">
        <li class="feature">
          <span class="feature__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16l-6 7.5V19l-4 1.5v-8L4 5z"/></svg>
          </span>
          <div class="feature__body">
            <p class="feature__num">01 &middot; Qualify</p>
            <h3>Know who to call first.</h3>
            <p>Services, timeline, budget and project details arrive with every lead, so the best jobs get your call before anyone else does.</p>
            <div class="feature__demo" aria-hidden="true">
              <span class="chip chip--blue">Pavers</span><span class="chip">$10k&ndash;25k</span><span class="chip">Start ASAP</span>
            </div>
          </div>
        </li>
        <li class="feature">
          <span class="feature__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>
          </span>
          <div class="feature__body">
            <p class="feature__num">02 &middot; Convert</p>
            <h3>Give them a reason to ask today.</h3>
            <p>Limited-time offers with real expiry dates sit right on top of the form, where they actually get claimed.</p>
            <div class="feature__demo" aria-hidden="true">
              <span class="offer-chip"><strong>Spring Backyard Special</strong><em>Ends June 30</em></span>
            </div>
          </div>
        </li>
        <li class="feature">
          <span class="feature__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
          </span>
          <div class="feature__body">
            <p class="feature__num">03 &middot; Win local</p>
            <h3>Look like the obvious local pick.</h3>
            <p>Service cities, zip codes, project photos and reviews build trust before they ever pick up the phone.</p>
            <div class="feature__demo" aria-hidden="true">
              <span class="chip">Tempe</span><span class="chip">Mesa</span><span class="chip">85281</span><span class="chip">85201</span><span class="chip chip--star">&#9733; 4.9</span>
            </div>
          </div>
        </li>
      </ol>

      <p class="split__note">Every lead lands in your dashboard &mdash; track it from new to won.</p>

      <div class="hero__ctas">
        <a class="btn btn--primary btn--xl" href="/members/signup">Get My Free Lead Page</a>
        <a class="btn btn--ghost btn--xl" href="/how-it-works">See How It Works</a>
      </div>
    </div>
  </div>
</section>

<section class="section section--soft" id="calculator">
  <div class="container">
    <div class="center" style="margin-bottom:32px">
      <span class="eyebrow">Lead calculator</span>
      <h2>What Is a Better Form <span class="accent">Worth to You?</span></h2>
      <p class="lede" style="margin:0 auto">Move the sliders to match your business and see the leads, jobs and profit a form that converts 3&times; better could add.</p>
    </div>
    <?= View::render('partials/lead-calculator') ?>
  </div>
</section>

<section class="section close-band" id="free">
  <div class="container">
    <div class="close-head">
      <p class="close-tag">Free account &middot; no credit card</p>
      <h2 class="close-title">Get leads <em>like a pro</em>.<br>Start free today.</h2>
      <p class="close-lede">If you run a small service business, leads are not a marketing extra you get to later.
        They are what keeps the trucks rolling. Every reason below is one you already feel &mdash; the free account
        is just how you stop letting visitors slip away.</p>
    </div>
    <div class="close-grid">
      <?php foreach ([
        ['<path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-6"/>', 'More jobs from the traffic you already have',
         'Your vans, signs, ads and Google listing already send people your way. A form that asks the right questions turns more of those visitors into quote requests &mdash; without spending another dollar on ads.', false],
        ['<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>', 'Know who to call first',
         'Services, timeline and budget arrive with every lead, so the big project that wants to start this month gets your call before the tire-kicker does.', false],
        ['<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C10 3 12 8 12 8s2-5 4.5-5a2.5 2.5 0 0 1 0 5"/>', 'Offers that actually get claimed',
         'Your special sits right on top of the form with a real expiry date. It gives a visitor a reason to ask for a quote today instead of &ldquo;some time&rdquo;.', false],
        ['<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3 6.4 20.2l1.1-6.2L3 9.6l6.2-.9z"/>', 'Look established before they ever call',
         'Photos of your work, reviews from real customers, your service cities and zip codes. A stranger sees a business that has done this a hundred times.', false],
        ['<path d="M12 2v20"/><path d="M17 6.5C17 4.6 14.8 3.5 12 3.5S7 4.6 7 6.8c0 4.7 10 2.7 10 7.7 0 2.3-2.2 3.5-5 3.5s-5-1.2-5-3.5"/>', 'Stop competing on price alone',
         'A page that shows your work and your offer gives people a reason to pick your quote, not just the cheapest one. Without it, price is all they have to compare.', false],
        ['<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>', 'Keep up with the shop down the road',
         'Your competitors are making it easy to ask for a quote. If a customer has to hunt for your phone number, they ask the next business on the list instead.', false],
        ['<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.1z"/>', 'Never lose a lead in your inbox',
         'Every request lands in one dashboard. Mark it contacted, quoted, won or lost, add notes and export to CSV &mdash; nothing slips through the cracks.', false],
        ['<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>', 'One link you can share everywhere',
         'Put it on Google Business, Facebook, Nextdoor, text messages, flyers and truck wraps. Pro and Premium pages are listed on MonsterList too.', false],
        ['<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>', 'Free tools, and a team that builds it',
         'Your Million Dollar Lead Form page, written and designed for you by our team, with ' . App\Support\Plans::FREE_MONTHLY_LEADS . ' leads a month on the free plan. Free for as long as you want it, no card on file.', true],
      ] as [$icon, $title, $body, $lead]): ?>
        <div class="close-card<?= $lead ? ' close-card--lead' : '' ?>">
          <span class="close-card__tile" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $icon ?></svg></span>
          <h3><?= $title ?></h3>
          <p><?= $body ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="close-act">
      <p class="close-rule">
        <span class="close-rule__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg></span>
        <span><strong>And we keep it honest: your leads are yours.</strong>
          We never sell, share or resell the people who fill in your form, and we never pass them to a competitor.
          Every request goes to you, and only you, on every plan.</span>
      </p>
      <a class="close-btn" href="/members/signup">Create your free account &rarr;</a>
      <ul class="close-facts">
        <li>No credit card</li>
        <li>Free is a plan, not a trial</li>
        <li>We build your page for you</li>
      </ul>

      <div class="close-embed">
        <p><strong>Already have a website?</strong> Premium members paste one line of code to put the full page &mdash;
          or just the lead form &mdash; on their own site. WordPress, Wix, Squarespace or plain HTML.</p>
        <pre><code>&lt;script src="<?= View::e(View::url('/widget/your-business.js')) ?>" async&gt;&lt;/script&gt;</code></pre>
      </div>
    </div>
  </div>
</section>

<section class="section section--soft center">
  <div class="container">
    <h2>Ready for more quote requests?</h2>
    <p class="lede" style="margin:0 auto 22px">Your free page is a few minutes away.</p>
    <a class="btn btn--primary" href="/members/signup">Get Your Free Page &rarr;</a>
  </div>
</section>
