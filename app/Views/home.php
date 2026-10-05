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

      <div class="split__cards">
        <?php foreach ([
          ['01', '&#9997;', 'Qualify', 'Services, timeline, budget and project details on every lead &mdash; so you know who to call first.'],
          ['02', '&#127873;', 'Convert', 'Limited-time offers with expiry dates, right on top of the form where they get claimed.'],
          ['03', '&#128205;', 'Win local', 'Service cities, zip codes, photos, reviews and video build trust before they ever call.'],
        ] as [$num, $icon, $title, $body]): ?>
          <div class="step-card">
            <span class="step-card__icon" aria-hidden="true"><?= $icon ?></span>
            <span class="step-card__num"><?= $num ?></span>
            <h3><?= $title ?></h3>
            <p><?= $body ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <ul class="split__pills">
        <li>Services</li><li>Timeline</li><li>Budget</li><li>Offers</li><li>Zip codes</li>
      </ul>

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
      <span class="eyebrow">&#128176; Lead calculator</span>
      <h2>What Is a Better Form <span class="accent">Worth to You?</span></h2>
      <p class="lede" style="margin:0 auto">Move the sliders to match your business and see the leads, jobs and profit a form that converts 3&times; better could add.</p>
    </div>
    <?= View::render('partials/lead-calculator') ?>
  </div>
</section>

<section class="section close-band" id="free">
  <div class="container">
    <div class="close-head">
      <p class="close-tag">&#10022; Free account &middot; no credit card</p>
      <h2 class="close-title">Get leads <em>like a pro</em>.<br>Start free today.</h2>
      <p class="close-lede">If you run a small service business, leads are not a marketing extra you get to later.
        They are what keeps the trucks rolling. Every reason below is one you already feel &mdash; the free account
        is just how you stop letting visitors slip away.</p>
    </div>
    <div class="close-grid">
      <?php foreach ([
        ['&#128200;', 'More jobs from the traffic you already have',
         'Your vans, signs, ads and Google listing already send people your way. A form that asks the right questions turns more of those visitors into quote requests &mdash; without spending another dollar on ads.', false],
        ['&#127919;', 'Know who to call first',
         'Services, timeline and budget arrive with every lead, so the big project that wants to start this month gets your call before the tire-kicker does.', false],
        ['&#127873;', 'Offers that actually get claimed',
         'Your special sits right on top of the form with a real expiry date. It gives a visitor a reason to ask for a quote today instead of &ldquo;some time&rdquo;.', false],
        ['&#11088;', 'Look established before they ever call',
         'Photos of your work, reviews from real customers, your service cities and zip codes. A stranger sees a business that has done this a hundred times.', false],
        ['&#128176;', 'Stop competing on price alone',
         'A page that shows your work and your offer gives people a reason to pick your quote, not just the cheapest one. Without it, price is all they have to compare.', false],
        ['&#128205;', 'Keep up with the shop down the road',
         'Your competitors are making it easy to ask for a quote. If a customer has to hunt for your phone number, they ask the next business on the list instead.', false],
        ['&#128229;', 'Never lose a lead in your inbox',
         'Every request lands in one dashboard. Mark it contacted, quoted, won or lost, add notes and export to CSV &mdash; nothing slips through the cracks.', false],
        ['&#128279;', 'One link you can share everywhere',
         'Put it on Google Business, Facebook, Nextdoor, text messages, flyers and truck wraps. Pro and Premium pages are listed on MonsterList too.', false],
        ['&#9889;', 'Free tools, and a team that builds it',
         'Your Million Dollar Lead Form page, written and designed for you by our team, with ' . App\Support\Plans::FREE_MONTHLY_LEADS . ' leads a month on the free plan. Free for as long as you want it, no card on file.', true],
      ] as [$icon, $title, $body, $lead]): ?>
        <div class="close-card<?= $lead ? ' close-card--lead' : '' ?>">
          <span class="close-card__tile" aria-hidden="true"><?= $icon ?></span>
          <h3><?= $title ?></h3>
          <p><?= $body ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="close-act">
      <p class="close-rule">
        <span class="close-rule__icon" aria-hidden="true">&#128737;</span>
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
